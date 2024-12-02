<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use DI\ContainerBuilder;
use Liquid\Core\Model\AppConfig;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Config\PrimaryConfigFileResolver;
use Liquid\Framework\App\Config\Reader;
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
use Liquid\Framework\Logger\LoggerProxy;
use Liquid\Framework\Serialize\Serializer\Json;
use Liquid\Framework\Serialize\Serializer\Serialize;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\View\TemplateEngine;
use Psr\Log\LoggerInterface;

class ObjectManagerFactory
{
    /**
     * Initialization parameter for custom deployment configuration data
     */
    public const INIT_PARAM_DEPLOYMENT_CONFIG = 'LQ_CONFIG';

    public function __construct(private readonly DirectoryList $directoryList)
    {

    }

    public function create(array $arguments): ObjectManagerInterface
    {
        $appMode = isset($arguments[State::PARAM_MODE]) ? $arguments[State::PARAM_MODE] : AppMode::Production;


        $appConfig = $this->createAppConfig($this->directoryList, $arguments);

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
            AppConfig::class => $appConfig,
            Config::class => $diConfig,
            //   CacheItemPoolInterface::class => $cachePool,
            //    Profiler::class => $this->profiler,
            //  ComponentRegistrarInterface::class => ComponentRegistrar::class,
            DirectoryList::class => $this->directoryList,
            // ConfigLoader::class => $configLoader,
        ];


        $appConfig->setValue('site_url', 'http://localhost:8901');
//        $this->data['site_url'] = $this->automaticallyDetectSiteUrl();
//
//        if (!$this->isCLI()) {
//            $this->data['current_url'] = $this->detectCurrentUrl();
//        }
//
        $appConfig->setValue('app_url', 'https://app.attlaz.com/');
        $appConfig->setValue('status_url', 'https://status.attlaz.com/');
        $appConfig->setValue('documentation_url', 'https://docs.attlaz.com/');
        $appConfig->setValue('api_reference_url', 'https://app.swaggerhub.com/apis-docs/Echron/attlaz-api/');
        $appConfig->setValue('signup_url', 'https://app.attlaz.com/signup');
        $appConfig->setValue('dev', [
            'minifyhtml' => true,
            'minifycss' => true,
        ]);

        /**
         * Build first with initial config
         */
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->addDefinitions($diConfig->getDefinitions());
        $containerBuilder->addDefinitions($sharedInstances);

        $container = $containerBuilder->build();
        $objectManager = new ObjectManager($container, $diConfig);
        $container->set(ObjectManagerInterface::class, $objectManager);

        /**
         * Load additional config (module configuration)
         */
        $moduleFileReader = $objectManager->get(\Liquid\Framework\Module\File\Reader::class);
        $diConfig->extend($this->loadEnvironmentConfig($this->directoryList, $moduleFileReader));

        /**
         * // TODO: is it possibe to re-use already build instances? Do we actually want this?
         * Build container again
         */
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->addDefinitions($sharedInstances);
        $containerBuilder->addDefinitions($diConfig->getDefinitions());

        $container = $containerBuilder->build();

        $objectManager = new ObjectManager($container, $diConfig);
        $container->set(ObjectManagerInterface::class, $objectManager);

        return $objectManager;

    }

    private function createAppConfig(DirectoryList $directoryList, array $arguments): AppConfig
    {
        $customData = isset($arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG])
            ? $arguments[self::INIT_PARAM_DEPLOYMENT_CONFIG]
            : [];

        $reader = new Reader($directoryList);
        return new AppConfig($reader, $customData);
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

    private function createContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
        // $containerBuilder->enableDefinitionCache('lq');
        // $containerBuilder->enableCompilation(ROOT . 'var/cache');
        return $containerBuilder;
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
        $configData = null;
        try {

            $fileSystem = new Filesystem($directoryList);
            $fileResolver = new FileResolver($moduleFileReader, $fileSystem);

            $reader = new FileSystemReader($fileResolver, 'di.php');

            $configData = $reader->read('global');

        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
        return $configData;
    }
}
