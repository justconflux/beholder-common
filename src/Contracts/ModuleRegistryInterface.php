<?php

namespace Beholder\Common\Contracts;

use Beholder\Common\Irc\Channel;

interface ModuleRegistryInterface
{
    public function getGlobalCommandNamespace(): CommandNamespace;

    /**
     * @param array<string> $namespace
     * @return CommandNamespace
     */
    public function findOrRegisterCommandNamespace(array $namespace): CommandNamespace;

    public function registerEventSubscriber(string $event, callable $callback): void;

    public function addChannelRequirement(BeholderModule $owner, Channel $channel): void;

    public function removeChannelRequirement(BeholderModule $owner, Channel $channel): void;
}
