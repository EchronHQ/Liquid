<?php
declare(strict_types=1);

namespace Liquid\Framework\Event\Config;

use Liquid\Framework\App\Cache\Type\Config;
use Liquid\Framework\Config\Data\ScopedData;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;

class Data extends ScopedData
{
    private string $cacheId = 'event_config_cache';

    public function __construct(
        EventConfigReader   $reader,
        Config              $cache,
        SerializerInterface $serializer
    )
    {
        parent::__construct(
            $reader,
            $cache,
            $serializer,
            $this->cacheId
        );
    }

    /**
     * @param string|null $path
     * @param mixed|null $default
     * @return EventListenerData[]
     */
    public function get(string|null $path = null, mixed $default = null): array
    {
        return parent::get($path, $default);
    }
}
