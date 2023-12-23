<?php

namespace Beholder\Common\Traits\Modules;

use Beholder\Common\Contracts\ModuleRegistryInterface;

trait HasModuleRegistry
{
    protected ModuleRegistryInterface $moduleRegistry;

    public function setModuleRegistry(
        ModuleRegistryInterface $moduleRegistry,
    ): void
    {
        $this->moduleRegistry = $moduleRegistry;
    }
}
