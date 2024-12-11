<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Cache\CacheState;
use Liquid\Framework\App\Cache\CacheStateInterface;
use Liquid\Framework\App\Config\PrimaryConfigFileResolver;
use Liquid\Framework\App\Config\Reader;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Config\SegmentConfigInterface;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\App\Entity\AggregateEntityResolver;
use Liquid\Framework\App\Entity\EntityResolverInterface;
use Liquid\Framework\App\State;
use Liquid\Framework\Component\ComponentRegistrar;
use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Config\FileResolver;
use Liquid\Framework\Config\FileResolverInterface;
use Liquid\Framework\Config\FileSystemReader;
use Liquid\Framework\Escaper;
use Liquid\Framework\Exception\RuntimeException;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\Filesystem\Filesystem;
use Liquid\Framework\Locale\Formatter;
use Liquid\Framework\Locale\Resolver;
use Liquid\Framework\Locale\ResolverInterface;
use Liquid\Framework\Logger\LoggerProxy;
use Liquid\Framework\Serialize\Serializer\Json;
use Liquid\Framework\Serialize\Serializer\Serialize;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\Url\ScopeResolver;
use Liquid\Framework\Url\ScopeResolverInterface;
use Liquid\Framework\View\TemplateEngine;
use Psr\Log\LoggerInterface;

class ObjectManagerFactory
{
    /**
     * Initialization parameter for custom deployment configuration data
     */
    public const string INIT_PARAM_DEPLOYMENT_CONFIG = 'LQ_CONFIG';

    public function __construct(private readonly DirectoryList $directoryList)
    {

    }

    public function create(array $arguments): ObjectManagerInterface
    {
        $appMode = isset($arguments[State::PARAM_MODE]) ? $arguments[State::PARAM_MODE] : AppMode::Production;


        $deploymentConfig = $this->createDeploymentConfig($this->directoryList, $arguments);

        // $cachePool = $this->buildCachePool();

        $diConfig = new Config();

        //   if ($env->getMode() != Environment\Compiled::MODE) {
        $configData = $this->_loadPrimaryConfig($this->directoryList);


        if ($configData) {
            $diConfig->extend($configData);

        }
        $diConfig->extend([
            'preferences' => [
                LoggerInterface::class => LoggerProxy::class,
                ComponentRegistrarInterface::class => ComponentRegistrar::class,
                FileResolverInterface::class => FileResolver::class,
                ObjectManagerInterface::class => ObjectManager::class,
                SerializerInterface::class => Json::class,
                EntityResolverInterface::class => AggregateEntityResolver::class,
                CacheStateInterface::class => CacheState::class,
                SegmentConfigInterface::class => ScopeConfig::class,
                ResolverInterface::class => Resolver::class,
                ScopeResolverInterface::class => ScopeResolver::class,
            ],
            'types' => [
                ConfigLoader::class => [
                    'arguments' => [
                        ['name' => 'cache', 'type' => 'object', 'value' => \Liquid\Framework\App\Cache\Type\Config::class],
                        ['name' => 'fileSystemReader', 'type' => 'object', 'value' => \Liquid\Framework\ObjectManager\Config\FileSystem::class],
                        ['name' => 'serializer', 'type' => 'object', 'value' => Serialize::class],
                    ],
                ],
                // TODO: move this to a general DI file
                TemplateEngine::class => [
                    'arguments' => [
                        'blockVariables' => [
                            'name' => 'blockVariables',
                            'type' => 'array',
                            'value' => [
                                'escaper' => ['type' => 'object', 'value' => Escaper::class],
                                'locale' => ['type' => 'object', 'value' => Formatter::class],
                                'logger' => ['type' => 'object', 'value' => LoggerInterface::class],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

// Load DI based on environment


        //  }
//$cache =
//        $configLoader = new ConfigLoader($cache,$reader);

        $sharedInstances = [

            //  SqlRemoteService::class => $this->buildSQL(),
            DeploymentConfig::class => $deploymentConfig,
            Config::class => $diConfig,
            //   CacheItemPoolInterface::class => $cachePool,
            //    Profiler::class => $this->profiler,
            //  ComponentRegistrarInterface::class => ComponentRegistrar::class,
            DirectoryList::class => $this->directoryList,
            // ConfigLoader::class => $configLoader,
        ];


        $deploymentConfig->setValue('site_url', 'http://localhost:8901');
//        $this->data['site_url'] = $this->automaticallyDetectSiteUrl();
//
//        if (!$this->isCLI()) {
//            $this->data['current_url'] = $this->detectCurrentUrl();
//        }
//


        /**
         * Build first with initial config
         */
//        $containerBuilder = $this->createContainerBuilder();
//        $containerBuilder->addDefinitions($diConfig->getDefinitions());
//        $containerBuilder->addDefinitions($sharedInstances);
//
//        $container = $containerBuilder->build();
        $objectManager = new ObjectManager($diConfig, $sharedInstances);


        /**
         * Load additional config (module configuration)
         */
        $moduleFileReader = $objectManager->get(\Liquid\Framework\Module\File\Reader::class);
        $objectManager->configure($this->loadEnvironmentConfig($this->directoryList, $moduleFileReader));

        /**
         * // TODO: is it possibe to re-use already build instances? Do we actually want this?
         * Build container again
         */


        return $objectManager;

    }

    private function createDeploymentConfig(
        DirectoryList $directoryList,
        array         $arguments
    ): DeploymentConfig
    {
        $customData = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG]
            : [];

        $reader = new Reader($directoryList);
        return new DeploymentConfig($reader, $customData);
    }

    /**
     * Load primary config
     *
     * @param DirectoryList $directoryList
     * @return array
     * @throws RuntimeException
     */
    private function _loadPrimaryConfig(DirectoryList $directoryList): array
    {
        $configData = null;
        try {

            $fileSystem = new Filesystem($directoryList);
            $fileResolver = new PrimaryConfigFileResolver($fileSystem);

            $reader = new FileSystemReader($fileResolver, 'di.php');

            $configData = $reader->read('primary');
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
        return $configData;
    }


//    /**
//     * TODO: move this to cacheFactory class
//     * @return CacheItemPoolInterface
//     * @throws \RedisException
//     */

//    private function buildCachePool(): CacheItemPoolInterface
//    {
//        $cacheBackend = $this->config->getValue('cache.storage.backend', 'cache');
//        switch ($cacheBackend) {
//            case 'redis':
//                $host = $this->config->getValueString('cache.storage.host');
//
//                $client = new \Redis();
//                $client->connect($host);
//
//                return new RedisAdapter($client);
//            case 'array':
//                return new ArrayAdapter();
//        }
//        throw new \Error('Invalid cache');
//
//
//    }

    /**
     * Load environment config
     *
     * @param DirectoryList $directoryList
     * @param \Liquid\Framework\Module\File\Reader $moduleFileReader
     * @return array
     * @throws RuntimeException
     */
    private function loadEnvironmentConfig(DirectoryList $directoryList, \Liquid\Framework\Module\File\Reader $moduleFileReader): array
    {

        try {

            $fileSystem = new Filesystem($directoryList);
            $fileResolver = new FileResolver($moduleFileReader, $fileSystem);

            $reader = new FileSystemReader($fileResolver, 'di.php');

            return $reader->read('global');
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
