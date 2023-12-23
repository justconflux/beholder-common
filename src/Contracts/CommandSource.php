<?php

namespace Beholder\Common\Contracts;

use Beholder\Common\Irc\Channel;
use Beholder\Common\Irc\Nick;

interface CommandSource
{
    public function isChannel(): bool;

    public function reply(string $message): void;

    public function getChannel(): Channel;

    public function getNick(): Nick;
}
