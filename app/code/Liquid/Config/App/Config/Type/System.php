<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Type;

use Liquid\Content\Model\Segment\SegmentInterface;
use Liquid\Framework\App\Cache\CacheStateInterface;
use Liquid\Framework\App\Config\ConfigTypeInterface;
use Liquid\Framework\Cache\FrontendInterface;
use Liquid\Framework\Encryption\Encryptor;
use Liquid\Framework\Exception\RuntimeException;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;

class System implements ConfigTypeInterface
{
    /**
     * System config type.
     */
    public const string CONFIG_TYPE = 'system';
    // TODO: further implement this
    private array $values = [


        // TODO: read this from yaml, database or other config sources
        'documentation_url' => 'https://docs.attlaz.com/',
        'status_url' => 'https://status.attlaz.com/',
        'app_url' => 'https://app.attlaz.com/',
        'api_reference_url' => 'https://app.swaggerhub.com/apis-docs/Echron/attlaz-api/',
        'signup_url' => 'https://app.attlaz.com/signup',
    ];
    /**
     * @var array
     */
    private array $data = [];
    /**
     * The type of config.
     *
     * @var string
     */
    private string $configType;
    /**
     * List of scopes that were retrieved from configuration storage
     *
     * Is used to make sure that we don't try to load non-existing configuration scopes.
     *
     * @var array
     */
    private array|null $availableDataScopes = null;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly CacheStateInterface $cacheState,
        private readonly FrontendInterface   $cache,
        private readonly Encryptor           $encryptor,
        private readonly SystemConfigReader  $reader
    )
    {

    }

    public function get(string $path = ''): array|int|string|bool|null
    {
        if ($path === '') {
            throw new RuntimeException('Get system path with empty path not implemented');
//            $this->data = $this->loadAllData();
//            return $this->data;
        }

        return $this->getWithParts($path);
//        if ($path === '') {

//            $this->data = $this->loadAllData();
//            return $this->data;
//        }

        //  return $this->getWithParts($path);
        return $this->values[$path];
    }

    public function clean(): void
    {
        // TODO: Implement clean() method.
    }

    private function getWithParts(string $path): array|int|string|bool|null
    {

        $pathParts = explode('/', $path);
        if (count($pathParts) === 1 && $pathParts[0] !== SegmentInterface::SEGMENT_DEFAULT) {
            if (!isset($this->data[$pathParts[0]])) {
                $this->readData();
            }

            return $this->data[$pathParts[0]];
        }
        $segmentType = array_shift($pathParts);
        if ($segmentType === SegmentInterface::SEGMENT_DEFAULT) {
            if (!isset($this->data[$segmentType])) {
                $scopeData = $this->loadDefaultScopeData() ?? [];
                $this->setDataBySegmentType($segmentType, $scopeData);
            }

            return $this->getDataByPathParts($this->data[$segmentType], $pathParts);
        }
        $segmentId = array_shift($pathParts);

        ///    var_dump(['type' => $segmentType, 'segment' => $segmentId]);
        if (!isset($this->data[$segmentType][$segmentId])) {
            $scopeData = $this->loadScopeData($segmentType, $segmentId) ?? [];
            $this->setDataBySegmentId($segmentType, $segmentId, $scopeData);
        }
        return isset($this->data[$segmentType][$segmentId])
            ? $this->getDataByPathParts($this->data[$segmentType][$segmentId], $pathParts)
            : null;
    }


    /**
     * The freshly read data.
     *
     * @return array
     */
    private function readData(): array
    {
        $this->data = $this->reader->read();
//        var_dump($this->data);
//        $this->data = $this->postProcessor->process(
//            $this->data
//        );

        return $this->data;
    }

    /**
     * Load configuration data for default scope.
     *
     * @return array
     */
    private function loadDefaultScopeData(): array
    {
        if (!$this->cacheState->isEnabled(\Liquid\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER)) {
            return $this->readData();
        }
        $loadAction = function () {
            $scopeType = SegmentInterface::SEGMENT_DEFAULT;
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType);
            $scopeData = false;
            if ($cachedData !== false) {
                try {
                    $scopeData = [$scopeType => $this->serializer->unserialize($this->encryptor->decrypt($cachedData))];
                } catch (\InvalidArgumentException $e) {
                    // $this->logger->warning($e->getMessage());
                    $scopeData = false;
                }
            }
            return $scopeData;
        };

        return $loadAction();
    }

    /**
     * Load configuration data for a specified scope.
     *
     * @param string $scopeType
     * @param string $scopeId
     * @return array
     */
    private function loadScopeData(string $scopeType, string $scopeId): array
    {
        if (!$this->cacheState->isEnabled(\Liquid\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER)) {
            return $this->readData();
        }

        $loadAction = function () use ($scopeType, $scopeId) {
            /* Note: configType . '_scopes' needs to be loaded first to avoid race condition where cache finishes
               saving after configType . '_' . $scopeType . '_' . $scopeId but before configType . '_scopes'. */
            $cachedScopeData = $this->cache->load($this->configType . '_scopes');
            $cachedData = $this->cache->load($this->configType . '_' . $scopeType . '_' . $scopeId);
            $scopeData = false;
            if ($cachedData === false) {
                if ($this->availableDataScopes === null) {
                    if ($cachedScopeData !== false) {
                        $serializedCachedData = $this->encryptor->decrypt($cachedScopeData);
                        $this->availableDataScopes = $this->serializer->unserialize($serializedCachedData);
                    }
                }
                if (is_array($this->availableDataScopes) && !isset($this->availableDataScopes[$scopeType][$scopeId])) {
                    $scopeData = [$scopeType => [$scopeId => []]];
                }
            } else {
                $serializedCachedData = $this->encryptor->decrypt($cachedData);
                $scopeData = [$scopeType => [$scopeId => $this->serializer->unserialize($serializedCachedData)]];
            }

            return $scopeData;
        };
    }

    /**
     * Walk nested hash map by keys from $pathParts.
     *
     * @param array $data to walk in
     * @param array $pathParts keys path
     * @return array|int|string|bool|null
     */
    private function getDataByPathParts(array $data, array $pathParts): array|int|string|bool|null
    {
        foreach ($pathParts as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof \Liquid\Framework\DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Sets data according to scope type.
     *
     * @param string|null $segmentType
     * @param array $segmentData
     * @return void
     */
    private function setDataBySegmentType(string|null $segmentType, array $segmentData): void
    {
        if (!isset($this->data[$segmentType])) {
            if (isset($segmentData[$segmentType])) {
                $this->data[$segmentType] = $segmentData[$segmentType];
            } else {
                $this->data[$segmentType] = [];
            }
        }
    }

    /**
     * Sets data according to segment type and id.
     *
     * @param string|null $segmentType
     * @param string|null $segmentId
     * @param array $segmentData
     * @return void
     */
    private function setDataBySegmentId(string|null $segmentType, string|null $segmentId, array $segmentData): void
    {
        if (!isset($this->data[$segmentType][$segmentId]) && isset($segmentData[$segmentType][$segmentId])) {
            $this->data[$segmentType][$segmentId] = $segmentData[$segmentType][$segmentId];
        }
    }
}
