<?php

declare(strict_types=1);

namespace trinity\cache\redis;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Redis;

final class RedisCacheItemPool implements CacheItemPoolInterface
{
    private array $deferredItems;

    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        $item = new RedisCacheItem($key);
        $value = $this->redis->get($key);

        if ($value !== false) {
            $item->set(unserialize($value));
            $item->expiresAt($this->redis->ttl($key) > 0 ? new \DateTimeImmutable('+' . $this->redis->ttl($key) . ' seconds') : null);
            $item->setIsHit(true);
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): iterable
    {
        if (count($keys) === 0) {
            return $this->getAllItems();
        }

        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return $this->redis->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $deletedCount = $this->redis->del($keys);

        return $deletedCount === count($keys);
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $value = $item->get();
        $expiration = $item->getExpiration();

        $seconds = null;

        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }

        if ($seconds === null) {
            return $this->redis->set($key, $value);
        }

        if ($expiration instanceof \DateTimeInterface) {
            $seconds = $expiration->getTimestamp() - time();
        }

        if ($seconds !== null && $seconds > 0) {
            return $this->redis->setex($key, $seconds, $value);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferredItems[] = $item;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        foreach ($this->deferredItems as $item) {
            if ($this->save($item) === false) {
                return false;
            }
        }

        $this->deferredItems = [];

        return true;
    }

    public function getAllItems(): array
    {
        $items = [];

        $keys = $this->getKeys();

        foreach ($keys as $key) {
            $item = $this->getItem($key);

            if ($item instanceof CacheItemInterface) {
                $items[$key] = $item;
            }
        }

        return $items;
    }

    public function getKeys(): array
    {
        $keys = $this->redis->keys('*');

        return $keys ?: [];
    }
}
