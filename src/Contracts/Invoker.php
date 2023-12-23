<?php

namespace Beholder\Common\Contracts;

use Beholder\Common\Irc\Nick;

interface Invoker
{
    public function getNick(): Nick;

    public function isAdmin(): bool;

    public function commandSource(): CommandSource;
}
