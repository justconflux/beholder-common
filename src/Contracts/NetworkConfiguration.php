<?php

namespace Beholder\Common\Contracts;

interface NetworkConfiguration
{
    public function getEnabledCapabilities(): array;

    public function hasEnabledCapability(string $capability): bool;

    public function getOption(string $option, mixed $defaultValue = null): mixed;

    public function getOptions(): array;

    public function inOptionValues(string $option, mixed $value): bool;

    public function inOptionKeys(string $option, mixed $value): bool;

    public function isChannel(string $nick): bool;

    public function isUser(string $nick): bool;
}
