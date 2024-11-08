<?php

namespace Beholder\Common\Persistence;

use Beholder\Common\Persistence\Exceptions\PdoPersistenceException;
use Beholder\Common\Persistence\Exceptions\PersistenceException;
use Exception;
use PDO as Base;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract class Pdo
{
    private string $hostname;
    private string $database;
    private string $username;
    private string $password;
    private bool $credentialsSet = false;
    private ?Base $activeConnection = null;

    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function setCredentials(
        string $hostname,
        string $username,
        string $password,
        string $database,
    ): void
    {
        if ($this->credentialsSet) {
            throw new PersistenceException('Redundant credentials');
        }

        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->credentialsSet = true;
    }

    protected function withDatabaseConnection(callable $fn): mixed
    {
        // If we're already connected, just use the existing connection
        if ($this->activeConnection) {
            return $fn($this->activeConnection);
        }

        // Otherwise, we'll manage a fresh connection for ourselves
        $this->activeConnection = $this->connect();

        $result = $fn($this->activeConnection);

        $this->activeConnection = null;

        return $result;
    }

    protected function withTransaction(callable $fn): mixed
    {
        return $this->withDatabaseConnection(
            function (Base $connectionResource) use ($fn) {
                $connectionResource->beginTransaction();

                try {
                    $result = $fn($connectionResource);
                    $connectionResource->commit();
                } catch (RuntimeException $exception) {
                    $connectionResource->rollback();
                    throw $exception;
                }

                return $result;
            },
        );
    }

    protected function checkSchema($schemaConfigKey): void
    {
        $this->withDatabaseConnection(
            function (Base $connectionResource) use ($schemaConfigKey) {
                $this->createCoreConfigTableIfMissing($connectionResource);

                $actualSchemaVersion = $this->getCurrentSchemaVersion($connectionResource, $schemaConfigKey);

                if (is_null($actualSchemaVersion)) {
                    // No entry, so we can assume the schema isn't set up.
                    $this->migrateSchema($connectionResource, $schemaConfigKey);
                    return;
                }

                $expectedSchemaVersion = $this->getLatestSchemaVersion();

                if ($actualSchemaVersion === $expectedSchemaVersion) {
                    // Schema version matches.
                    return;
                }

                if ($actualSchemaVersion < $expectedSchemaVersion) {
                    $this->migrateSchema(
                        $connectionResource,
                        $schemaConfigKey,
                        $actualSchemaVersion,
                    );
                    return;
                }

                throw new PersistenceException(
                    sprintf(
                        'Unexpected schema version (%s in use, %s expected)',
                        $actualSchemaVersion,
                        $expectedSchemaVersion,
                    ),
                );
            },
        );
    }

    /**
     * @return array<array<string>>
     */
    abstract protected function getSchema() : array;

    private function createCoreConfigTableIfMissing(Base $connectionResource): void
    {
        $result = $connectionResource->query(
            'SHOW TABLES LIKE "core_config"'
        );

        if (false === $result) {
            throw new PdoPersistenceException($connectionResource);
        }

        $isTableMissing = $result->rowCount() === 0;

        $result->closeCursor();

        if ($isTableMissing) {
            $this->createCoreConfigTable($connectionResource);
        }
    }

    private function createCoreConfigTable(Base $connectionResource): void
    {
        $result = $connectionResource->query(
            <<< EOD
            CREATE TABLE `core_config` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `config_key` VARCHAR(255) NOT NULL DEFAULT '',
                `config_value` VARCHAR(255) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`),
                UNIQUE KEY(`config_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;
            EOD,
        );

        if (false === $result) {
            throw new PdoPersistenceException($connectionResource);
        }
    }

    private function getCurrentSchemaVersion(Base $connectionResource, string $schemaConfigKey): ?int
    {
        $statement = $connectionResource->prepare(
            <<< EOD
            SELECT `config_value`
            FROM `core_config`
            WHERE `config_key` = :key
            LIMIT 1
            EOD,
        );

        $result = $statement->execute([
            'key' => $schemaConfigKey,
        ]);

        if (false === $result) {
            throw new PdoPersistenceException($connectionResource);
        }

        if ($statement->rowCount() === 0) {
            // No entry, so we can assume the schema isn't set up.
            return null;
        }

        $result = $statement->fetch(Base::FETCH_ASSOC);

        return (int) $result['config_value'];
    }

    private function getLatestSchemaVersion(): int
    {
        return max(array_keys($this->getSchema()));
    }

    /**
     * @return Base
     * @throws Exception
     */
    private function connect() : Base
    {
        $attempt = 1;
        $maxAttempts = 12;
        $connectionResource = null;
        do {
            if ($attempt > 1) {
                $this->logger->debug("Connecting to database...");
            }

            try {
                $connectionResource = new Base(
                    'mysql:dbname=' . $this->database . ';host=' . $this->hostname . ';charset=utf8mb4',
                    $this->username,
                    $this->password,
                );
            } catch (\PDOException $exception) {
                $this->logger->warning("Failed connecting to database (attempt $attempt of $maxAttempts)");
                sleep(5);
            }
        } while (is_null($connectionResource) && $attempt++ && $attempt < $maxAttempts);

        if (is_null($connectionResource)) {
            throw new Exception(
                'Could not connect to database',
                0,
                $exception ?? null,
            );
        }

        return $connectionResource;
    }

    private function migrateSchema(
        Base $connectionResource,
        string $subSchemaName,
        ?int $afterSchemaVersion = null,
    ): void
    {
        $appliedSchemaVersion = null;
        foreach ($this->getSchema() as $schemaVersion => $schemaCommands) {
            if (
                ! is_null($afterSchemaVersion)
                && $schemaVersion <= $afterSchemaVersion
            ) {
                // Migration has already been applied
                continue;
            }

            $this->applySchemaCommands($connectionResource, $schemaCommands);

            $appliedSchemaVersion = $schemaVersion;
        }

        if ($appliedSchemaVersion === null) {
            // No schema changes applied
            return;
        }

        $this->updateRecordedSchemaVersion($connectionResource, $subSchemaName, $appliedSchemaVersion);
    }

    /**
     * @param array<string> $schemaCommands
     */
    private function applySchemaCommands(Base $connectionResource, array $schemaCommands): void
    {
        foreach ($schemaCommands as $schemaCommand) {
            if (! $connectionResource->query($schemaCommand)) {
                throw new PdoPersistenceException(
                    $connectionResource,
                );
            }
        }
    }

    private function updateRecordedSchemaVersion(Base $connectionResource, string $subSchemaName, int $version): void
    {
        $statement = $connectionResource->prepare(
            <<< EOD
            INSERT INTO `core_config`
            SET `config_key` = :key,
            `config_value` = :value
            ON DUPLICATE KEY UPDATE `config_value` = :value;
            EOD,
        );

        $params = [
            'key' => $subSchemaName,
            'value' => $version,
        ];

        if (! $statement->execute($params)) {
            throw new PdoPersistenceException($connectionResource);
        }
    }
}
