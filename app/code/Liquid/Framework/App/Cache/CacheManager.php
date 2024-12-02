<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache;

class CacheManager
{
    private array $cacheTypeList = [

    ];

    /**
     * Cleans up caches
     *
     * @param array $types
     * @return void
     */
    public function clean(array $types)
    {
        foreach ($types as $type) {
            //$this->cacheTypeList->cleanType($type);
        }
    }

    /**
     * Presents summary about cache status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $result = [];
        foreach ($this->cacheTypeList as $type) {
            $result[$type['id']] = $type['status'];
        }
        return $result;
    }

    public function getAvailableTypes(): array
    {
        $result = [];
        foreach ($this->cacheTypeList as $type) {
            $result[] = $type['id'];
        }
        return $result;
    }
}
