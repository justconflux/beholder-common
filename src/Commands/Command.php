<?php

namespace Beholder\Common\Commands;

use Closure;
use InvalidArgumentException;

class Command
{
    readonly protected string $command;

    public function __construct(
        string $command,
        readonly protected string $description,
        readonly protected string $syntax,
        public Closure $callback,
    )
    {
        $this->assertValid($command);
        $this->command = $command;
    }

    public function __toString(): string
    {
        return $this->command;
    }

    public function normalize(): string
    {
        return strtolower($this->command);
    }

    public function equals(Command $command): bool
    {
        return $this->normalize() === $command->normalize();
    }

    public function matchesNamespace(NamespaceIdentifier $identifier): bool
    {
        return $this->normalize() === $identifier->normalize();
    }

    protected function assertValid(string $command): void
    {
        if (! preg_match('/^[a-zA-Z]+(-[a-zA-Z0-9]+)*$/', $command)) {
            throw new InvalidArgumentException();
        }
    }

    public function getCommand(): string
    {
        return $this->normalize();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSyntax(): string
    {
        return $this->syntax;
    }
}
