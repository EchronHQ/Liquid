<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache;

interface CacheStateInterface
{
    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @param string $cacheType
     * @return bool
     */
    public function isEnabled(string $cacheType): bool;

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function setEnabled(string $cacheType, bool $isEnabled): void;
}
