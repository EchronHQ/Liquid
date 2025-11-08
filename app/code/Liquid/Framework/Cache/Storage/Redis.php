<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache\Storage;

use Liquid\Framework\Cache\CacheCleanMode;
use Liquid\Framework\Cache\StorageInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class Redis implements StorageInterface
{
    private RedisAdapter|null $adapter = null;

    public function __construct(
        private readonly string      $host,
        private readonly int         $port,
        private readonly string|null $password = null
    )
    {


    }

    public function test(string $identifier): int|null
    {
        $item = $this->getAdapter()->getItem($identifier);
        // TODO: 0 is not the correct expiry time, can we get it with the meta data?
        return $item->isHit() ? 0 : null;
    }

    public function load(string $identifier): string|null
    {
        $item = $this->getAdapter()->getItem($identifier);
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
        return $this->getAdapter()->save($item);
    }

    public function remove(string $identifier): bool
    {
        return $this->getAdapter()->delete($identifier);
    }

    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool
    {
        if ($mode === CacheCleanMode::All) {
            return $this->getAdapter()->clear();
        }
        // TODO: this needs further implementation with tags
        throw new \RuntimeException('Redis->clean needs further implementation');
    }

    private function getAdapter(): RedisAdapter
    {
        if ($this->adapter === null) {
            if (!\class_exists(\Redis::class)) {
                // TODO: log this
                throw new \Exception('Redis class not found (is Redis extension installed?)');
            }
            throw new \Exception('This is going wrong!!');

            $client = new \Redis();
            $client->connect($this->host, $this->port);

            if (!empty($this->password)) {
                $client->auth($this->password);
            }


            $this->adapter = new RedisAdapter($client);

        }
        return $this->adapter;
    }
}
