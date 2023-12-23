<?php

namespace Beholder\Common\Contracts;

use Beholder\Common\Commands\Command;
use Beholder\Common\Commands\NamespaceIdentifier;

interface CommandNamespace
{
    public function registerNamespace(NamespaceIdentifier $identifier);

    public function registerCommand(Command $command): void;

    public function parent(): ?CommandNamespace;

    /**
     * @return array<CommandNamespace>
     */
    public function children(): array;
}
