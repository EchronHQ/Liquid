<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache;

use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\Cache\Storage\Factory;

class CachePool
{
    /**
     * Storage identifier associated with the default settings
     */
    public const DEFAULT_STORAGE_ID = 'default';
    /**
     * Config key for cache
     */
    public const KEY_CACHE = 'cache';

    public const KEY_STORAGE_CACHE = 'storage';

    /** @var StorageInterface[]|null */
    private array|null $instances = null;

    private array $storageSettings = [
        self::DEFAULT_STORAGE_ID => [],
    ];

    public function __construct(
        private readonly DeploymentConfig $appConfig,
        private readonly Factory          $factory
    )
    {

    }

    /**
     * Retrieve frontend instance by its unique identifier
     *
     * @param string $identifier Cache storage identifier
     * @return StorageInterface Cache storage instance
     * @throws \InvalidArgumentException
     */
    public function get(string $identifier): StorageInterface
    {
        $this->initialize();
        if (isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        if (!isset($this->instances[self::DEFAULT_STORAGE_ID])) {
            throw new \InvalidArgumentException(
                "Cache frontend '{$identifier}' is not recognized. As well as " .
                self::DEFAULT_STORAGE_ID .
                "cache is not configured"
            );
        }

        return $this->instances[self::DEFAULT_STORAGE_ID];
    }

    /**
     * Create instances of every cache frontend known to the system.
     *
     * Method is to be used for delayed initialization of the iterator.
     *
     * @return void
     */
    protected function initialize(): void
    {
        if ($this->instances === null) {
            $this->instances = [];
            foreach ($this->getCacheSettings() as $storageId => $frontendOptions) {
                $this->instances[$storageId] = $this->factory->create($frontendOptions);
            }
        }
    }

    private function getCacheSettings(): array
    {
        $cacheInfo = $this->appConfig->getConfigData(self::KEY_CACHE);
        if (null !== $cacheInfo && \array_key_exists(self::KEY_STORAGE_CACHE, $cacheInfo)) {
            return \array_replace_recursive($this->storageSettings, $cacheInfo[self::KEY_STORAGE_CACHE]);
        }
        return $this->storageSettings;
    }
}
