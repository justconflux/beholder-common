<?php

namespace Beholder\Common\Commands;

use InvalidArgumentException;

readonly class NamespaceIdentifier
{
    protected string $value;

    public function __construct(
        string $value,
    )
    {
        $this->assertValid($value);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function normalize(): string
    {
        return strtolower($this->value);
    }

    public function equals(NamespaceIdentifier $value): bool
    {
        return $this->normalize() === $value->normalize();
    }

    public function matchesCommand(Command $command): bool
    {
        return $this->normalize() === $command->normalize();
    }

    protected function assertValid(string $value): void
    {
        if (! preg_match('/^[a-zA-Z]+(-[a-zA-Z0-9]+)*$/', $value)) {
            throw new InvalidArgumentException();
        }
    }
}
