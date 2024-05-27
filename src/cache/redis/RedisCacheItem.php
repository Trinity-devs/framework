<?php

declare(strict_types=1);

namespace trinity\cache\redis;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

final class RedisCacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value = null;
    private ?DateTimeInterface $expiresAt;
    private bool $isHit;

    public function __construct(string $key)
    {
        $this->key = $key;
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

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        if ($time instanceof DateInterval === false && is_int($time) === false) {
            throw new InvalidArgumentException('Invalid time value provided.');
        }

        if ($time instanceof DateInterval) {
            $this->expiresAt = (new DateTimeImmutable())->add($time);
        }

        if (is_int($time)) {
            $this->expiresAt = (new DateTimeImmutable())->modify("+{$time} seconds");
        }

        return $this;
    }

    public function setIsHit(bool $isHit): void
    {
        $this->isHit = $isHit;
    }

    public function getExpiration(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }
}
