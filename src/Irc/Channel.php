<?php

namespace Beholder\Common\Irc;

readonly class Channel
{
    public function __construct(protected string $channel)
    {}

    public function __toString(): string
    {
        return $this->channel;
    }

    public function normalize(): string
    {
        return strtolower(trim($this->channel));
    }

    public function equals(Channel $channel): bool
    {
        return $this->normalize() === $channel->normalize();
    }

    public function isValid(): bool
    {
        // Must start with a valid channel prefix character (probably # or &)
        // Must not contain a space 0x20, a comma 0x2C, or a BELL/Ctrl+G 0x07.
        $validPrefixCharacters = '#&'; // TODO: Must get these from the connected network details
        return preg_match("/^[$validPrefixCharacters][^\x20\x2c\x07]+$/", $this->channel);
    }

    /**
     * @param array<Channel> $channels
     * @return bool
     */
    public function isIn(array $channels): bool
    {
        foreach ($channels as $channel) {
            if ($this->equals($channel)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Channel> $channels
     * @return bool
     */
    public function isNotIn(array $channels): bool
    {
        return ! $this->isIn($channels);
    }
}
