<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache\Storage;

use Liquid\Framework\Cache\CacheCleanMode;
use Liquid\Framework\Cache\StorageInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class Redis implements StorageInterface
{
    private RedisAdapter $adapter;

    public function __construct(string $host, int $port, string|null $password = null)
    {
        if (!\class_exists(\Redis::class)) {
            // TODO: log this
            return;
        }
        $client = new \Redis();
        $client->connect($host, $port);

        if (!empty($password)) {
            $client->auth($password);
        }


        $this->adapter = new RedisAdapter($client);


    }

    public function test(string $identifier): int|null
    {
        $item = $this->adapter->getItem($identifier);
        // TODO: 0 is not the correct expiry time, can we get it with the meta data?
        return $item->isHit() ? 0 : null;
    }

    public function load(string $identifier): string|null
    {
        $item = $this->adapter->getItem($identifier);
        if ($item->isHit()) {
            return $item->get();
        }
        return null;
    }

    public function save(string $data, string $identifier, array $tags = [], \DateInterval|int|null $lifeTime = null): bool
    {
        $item = new CacheItem();
        $item->set($data);
        $item->tag($tags);
        $item->expiresAfter($lifeTime);
        return $this->adapter->save($item);
    }

    public function remove(string $identifier): bool
    {
        return $this->adapter->delete($identifier);
    }

    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool
    {
        if ($mode === CacheCleanMode::All) {
            return $this->adapter->clear();
        }
        // TODO: this needs further implementation with tags
        throw new \RuntimeException('Redis->clean needs further implementation');
    }
}
