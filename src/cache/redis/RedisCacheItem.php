<?php

namespace trinity\cache\redis;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class RedisCacheItem implements CacheItemInterface
{
    private mixed $value;
    public int|null $ttl = null;
    public bool $isHit;

    public function __construct(private readonly string $key)
    {
        $this->isHit = false;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function expiresAt(DateTimeInterface|null $expiration): static
    {
        if ($expiration instanceof DateTimeInterface) {
            $now = new DateTime();
            $this->ttl = $expiration->getTimestamp() - $now->getTimestamp();

            return $this;
        }

        $this->ttl = null;

        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        if ($time instanceof DateInterval) {
            $this->ttl = (new DateTime())->add($time)->getTimestamp() - time();

            return $this;
        }

        if (is_numeric($time)) {
            $this->ttl = $time;

            return $this;
        }

        $this->ttl = null;

        return $this;
    }
}
