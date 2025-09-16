<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache\Storage;

use Liquid\Framework\Cache\StorageInterface;

class Factory
{
    public function create(array $options): StorageInterface
    {
        switch ($options['backend']) {
            case 'redis':

                $host = $options['host'];
                $port = $options['port'] ?? 6379;
                $password = $options['password'] ?? null;


                return new Redis($host, $port, $password);
                break;
            default:
                throw new \RuntimeException('Unknown cache storage "' . $options['backend'] . '"');
        }
    }
}
