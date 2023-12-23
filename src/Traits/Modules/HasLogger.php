<?php

namespace Beholder\Common\Traits\Modules;

use Psr\Log\LoggerInterface;

trait HasLogger
{
    // TODO: Ability to log to a file, but also to a channel/private message?
    protected LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
