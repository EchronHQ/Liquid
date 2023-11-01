<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheHelper
{
    public function __construct(private readonly CacheItemPoolInterface $cache)
    {

    }

    public function set(string $key, mixed $value, \DateInterval|null $ttl = null): bool
    {
        $key = $this->formatKey($key);

        $cacheKeys = $this->getKeys();


        $cacheKeys[] = $key;

        $this->setItem('cache-keys', $cacheKeys);
        return $this->setItem($key, $value, $ttl);

    }

    private function setItem(string $key, mixed $value, \DateInterval|null $ttl = null): bool
    {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($value);
        if ($ttl !== null) {
            $cacheItem->expiresAfter($ttl);
        }
        return $this->cache->save($cacheItem);
    }

    public function getItem(string $key): CacheItemInterface
    {
        $key = $this->formatKey($key);
        return $this->cache->getItem($key);
    }

    public function get(string $key): mixed
    {
        $key = $this->formatKey($key);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        return null;
    }

    public function unset(string $key): bool
    {
        $key = $this->formatKey($key);
        return $this->cache->deleteItem($key);
    }

    public function has(string $key): bool
    {
        $key = $this->formatKey($key);
        return $this->cache->hasItem($key);
    }

    /**
     * @param string[] $keys
     * @return CacheItemInterface[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItems(array $keys): array
    {
        $items = $this->cache->getItems($keys);

        $result = [];
        foreach ($items as $item) {
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @return string[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getKeys(): array
    {
        $item = $this->cache->getItem('cache-keys');
        if ($item->isHit()) {
            $value = $item->get();
            if (is_array($value)) {
                return $value;
            }
        }
        return [];
    }

    private function formatKey(string $key): string
    {
        return \str_replace(['.', '/', ':'], ['-', '-', '-'], $key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }
}
