<?php

declare(strict_types=1);

namespace trinity\cache\redis;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Redis;
use RedisException;

final class RedisCacheItemPool implements CacheItemPoolInterface
{
    private array $deferred = [];

    public function __construct(private readonly Redis $redis)
    {
    }

    /**
     * @param string $key
     * @return CacheItemInterface
     * @throws RedisException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $value = $this->redis->get($key);
        $item = new RedisCacheItem($key);

        if ($value !== false) {
            $item->set(unserialize($value))->isHit = true;
        }

        return $item;
    }

    /**
     * @param array $keys
     * @return iterable
     * @throws RedisException
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @param string $key
     * @return bool
     * @throws RedisException
     */
    public function hasItem(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    /**
     * @return bool
     * @throws RedisException
     */
    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * @param string $key
     * @return bool
     * @throws RedisException
     */
    public function deleteItem(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    /**
     * @param array $keys
     * @return bool
     * @throws RedisException
     */
    public function deleteItems(array $keys): bool
    {
        return $this->redis->del($keys) === count($keys);
    }

    /**
     * @param CacheItemInterface $item
     * @return bool
     * @throws RedisException
     */
    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $value = serialize($item->get());

        if ($item->ttl !== null) {
            return $this->redis->setex($key, $item->ttl, $value);
        }

        return $this->redis->set($key, $value);
    }

    /**
     * @param CacheItemInterface $item
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * @return bool
     * @throws RedisException
     */
    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        $this->deferred = [];

        return true;
    }
}
