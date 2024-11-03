<?php

namespace Beholder\Common\Irc;

readonly class Nick
{
    public function __construct(protected string $nick)
    {}

    public function __toString(): string
    {
        return $this->nick;
    }

    public function normalize(): string
    {
        return strtolower(trim($this->nick));
    }

    public function equals(Nick $nick): bool
    {
        return $this->normalize() === $nick->normalize();
    }

    public function isValid(): bool
    {
        // Must not start with a channel prefix character (probably # and &), or a colon
        // Must not contain a space 0x20.
        $channelPrefixCharacters = '#&'; // TODO: Must get these from the connected network details
        return preg_match("/^[^$channelPrefixCharacters][^\x20]+$/", $this->nick);
    }

    /**
     * @param array<Nick> $nicks
     * @return bool
     */
    public function isIn(array $nicks): bool
    {
        foreach ($nicks as $nick) {
            if ($this->equals($nick)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Nick> $nicks
     * @return bool
     */
    public function isNotIn(array $nicks): bool
    {
        return ! $this->isIn($nicks);
    }
}
