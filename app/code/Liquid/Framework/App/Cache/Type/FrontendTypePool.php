<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache\Type;

use Liquid\Framework\App\Config\AppConfig;
use Liquid\Framework\Cache\CachePool;
use Liquid\Framework\Cache\FrontendInterface;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class FrontendTypePool
{
    /**
     * Config key for cache
     */
    public const KEY_CACHE = 'cache';

    public const KEY_CACHE_TYPE = 'type';
    public const KEY_FRONTEND_CACHE = 'frontend';


    /** @var FrontendInterface[] */
    private array $instances = [];

    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly AppConfig              $appConfig,
        private readonly CachePool              $frontendPool
    )
    {

    }

    /**
     * Retrieve cache frontend instance by a cache type identifier, enforcing identifier-scoped access control
     *
     * @param string $cacheType Cache type identifier
     * @return FrontendInterface Cache frontend instance
     */
    public function get(string $cacheType): FrontendInterface
    {
        if (!isset($this->_instances[$cacheType])) {
            $frontendId = $this->_getCacheFrontendId($cacheType);
            $frontendInstance = $this->frontendPool->get($frontendId);
            /** @var $frontendInstance EnabledProxy */
            $frontendInstance = $this->objectManager->create(
                EnabledProxy::class,
                [
                    'frontend' => $frontendInstance,
                    'identifier' => $cacheType,
                ]
            );
            $this->instances[$cacheType] = $frontendInstance;
        }
        return $this->instances[$cacheType];
    }

    /**
     * Retrieve cache frontend identifier, associated with a cache type
     *
     * @param string $cacheType
     * @return string
     */
    protected function _getCacheFrontendId(string $cacheType): string
    {
        $result = null;
        $cacheInfo = $this->appConfig->getConfigData(self::KEY_CACHE);
        if (null !== $cacheInfo) {
            $result = isset($cacheInfo[self::KEY_CACHE_TYPE][$cacheType][self::KEY_FRONTEND_CACHE]) ?
                $cacheInfo[self::KEY_CACHE_TYPE][$cacheType][self::KEY_FRONTEND_CACHE] : null;
        }
        if (!$result) {
            if (isset($this->_typeFrontendMap[$cacheType])) {
                $result = $this->_typeFrontendMap[$cacheType];
            } else {
                $result = CachePool::DEFAULT_STORAGE_ID;
            }
        }
        return $result;
    }
}
