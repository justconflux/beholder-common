<?php

namespace Beholder\Common\Contracts;

interface BeholderModule
{
    public function setModuleRegistry(
        ModuleRegistryInterface $moduleRegistry,
    ): void;

    public function initialize(): void;

    public function boot(NetworkConfiguration $networkConfiguration): void;

    public function register(): void;
}
