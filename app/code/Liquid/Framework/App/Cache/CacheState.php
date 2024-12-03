<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache;

use Liquid\Framework\App\DeploymentConfig;

class CacheState implements CacheStateInterface
{
    /**
     * Deployment config key
     */
    public const string CACHE_KEY = 'cache_types';

    private array|null $statuses = null;

    public function __construct(
        private readonly DeploymentConfig $appConfig,
        private readonly bool             $disableAll = false
    )
    {
    }

    /**
     * Whether a cache type is enabled or not at the moment
     *
     * @param string $cacheType
     * @return bool
     */
    public function isEnabled(string $cacheType): bool
    {
        $this->load();
        return (bool)($this->statuses[$cacheType] ?? false);
    }

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function setEnabled(string $cacheType, bool $isEnabled): void
    {
        $this->load();
        $this->statuses[$cacheType] = (int)$isEnabled;
    }

    /**
     * Resets mutable state and/ or resources in objects that need to be cleaned after a response has been sent.
     */
    public function resetState(): void
    {
        $this->statuses = null;
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     */
    private function load(): void
    {
        if (null === $this->statuses) {
            $this->statuses = [];
            if ($this->disableAll) {
                return;
            }
            $this->statuses = $this->appConfig->getConfigData(self::CACHE_KEY) ?: [];
        }
    }
}
