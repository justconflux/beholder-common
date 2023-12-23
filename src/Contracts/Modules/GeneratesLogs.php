<?php

namespace Beholder\Common\Contracts\Modules;

use Psr\Log\LoggerInterface;

interface GeneratesLogs
{
    public function setLogger(LoggerInterface $logger): void;
}
