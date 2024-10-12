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
        // TODO: Needs a regex
        return preg_match('//', $this->channel); // Not sure if I should normalize before validating?
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
