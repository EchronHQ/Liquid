<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Cache\Type\Config;
use Liquid\Framework\Config\FileSystemReader;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;

class ConfigLoader
{

    public function __construct(
        private readonly Config              $cache,
        private readonly FileSystemReader    $fileSystemReader,
        private readonly SerializerInterface $serializer,
    )
    {

    }

    /**
     * Load modules DI configuration
     */
    public function load(AreaCode $area): array
    {
        $cacheId = $area->value . '::DiConfig';
        $data = $this->cache->load($cacheId);

        if (!$data) {
            $data = $this->fileSystemReader->read($area->value);
            $this->cache->save($this->serializer->serialize($data), $cacheId);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }
}
