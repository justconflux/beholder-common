<?php

namespace Beholder\Common\Traits;

trait FormatsIrcMessages
{
    protected function action(string $str): string
    {
        return "\x01" . 'ACTION ' . $str . "\x01";
    }

    protected function bold(string $str): string
    {
        return "\x02" . $str . "\x02";
    }

    protected function italic(string $str): string
    {
        return "\x1D" . $str . "\x1D";
    }

    protected function underline(string $str): string
    {
        return "\x1F" . $str . "\x1F";
    }

    protected function strikethrough(string $str): string
    {
        return "\x1E" . $str . "\x1E";
    }

    protected function monospace(string $str): string
    {
        return "\x11" . $str . "\x11";
    }

    // TODO: Colors
}
