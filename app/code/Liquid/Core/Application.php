<?php

declare(strict_types=1);

namespace Liquid\Core;

use Attlaz\Adapter\Base\RemoteService\SqlRemoteService;
use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Client;
use Attlaz\Model\Log\LogStreamId;
use DI\Container;
use DI\ContainerBuilder;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\ApplicationMode;
use Liquid\Framework\Component\ComponentRegistrar;
use Liquid\Framework\Component\ComponentRegistrarInterface;
use Monolog\ErrorHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class Application
{
    private Container $diContainer;
    private LoggerInterface|null $logger = null;
    private AppConfig $config;

    private readonly Profiler $profiler;

    public function __construct()
    {
        $this->profiler = new Profiler();
    }


    private function initLogger(): void
    {
        $this->logger = new Logger('Attlaz Site');

        ErrorHandler::register($this->logger);
        /**
         * Slack handler
         */
        $slackHook = $this->config->getValue('logger.slack.webhook');
        $slackChannel = $this->config->getValue('logger.slack.webhook');
        $slackUsername = $this->config->getValue('logger.slack.webhook');
        $slackHandler = new SlackWebhookHandler($slackHook, $slackChannel, $slackUsername, true, null, false, true);
        $slackHandler->setLevel(Level::Info);
        if ($this->config->getMode() !== ApplicationMode::DEVELOP) {
            $this->logger->pushHandler($slackHandler);
        }
        //                $this->logger->error('Run tests');
        //                return;


        if (!$this->config->isCLI() && $this->config->getMode() === ApplicationMode::DEVELOP) {
            $chromeHandler = new BrowserConsoleHandler();
            $this->logger->pushHandler($chromeHandler);
        }

        //        $cliHandler = new StreamHandler(fopen('php://stdout', 'wb'), Level::Debug);
        //        $htmlFormatter = new HtmlFormatter();
        //        $cliHandler->setFormatter($htmlFormatter);
        //        $this->logger->pushHandler($cliHandler);


        $attlazClientId = $this->config->getValue('attlaz_api.client_id');
        $attlazClientSecret = $this->config->getValue('attlaz_api.client_secret');
        $attlazApiEndpoint = $this->config->getValue('attlaz_api.endpoint');
        $client = new Client($attlazClientId, $attlazClientSecret, true);
        $client->setEndPoint($attlazApiEndpoint);
        $attlazHandler = new AttlazHandler($client, new LogStreamId('Vt9HtWRee'), Level::Info);
        $this->logger->pushHandler($attlazHandler);

        if ($this->config->isCLI()) {
            //Stream handler
            $cliHandler = new StreamHandler(\STDOUT, Level::Debug);
            $this->logger->pushHandler($cliHandler);
        } elseif ($this->config->getMode() === ApplicationMode::PRODUCTION) {

            $webProcessor = new WebProcessor();
            $this->logger->pushProcessor($webProcessor);
        }
    }

    private function buildCachePool(): CacheItemPoolInterface
    {
        $cacheBackend = $this->config->getValue('cache.backend', '');
        if ($cacheBackend === 'redis') {
            $client = new \Redis();
            $client->connect('redis');

            return new RedisAdapter($client);
        }

        return new ArrayAdapter();
    }

    private function buildSQL(): SqlRemoteService
    {
        $database = $this->config->getValue('database.database');
        $username = $this->config->getValue('database.username');
        $password = $this->config->getValue('database.password');
        $host = $this->config->getValue('database.host');
        return new SqlRemoteService($database, $username, $password, $host);
    }

    private function initDI(): void
    {


        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
//        $containerBuilder->enableCompilation(ROOT . 'var/cache');

        $cachePool = $this->buildCachePool();
        $containerBuilder->addDefinitions([
            LoggerInterface::class => $this->logger,
            SqlRemoteService::class => $this->buildSQL(),
            AppConfig::class => $this->config,
            CacheItemPoolInterface::class => $cachePool,
            Profiler::class => $this->profiler,
            ComponentRegistrarInterface::class => \DI\create(ComponentRegistrar::class),
        ]);
        $this->diContainer = $containerBuilder->build();
    }

    final protected function beforeRun(): void
    {
        $this->profiler->profilerStart('Application:beforeRun');

        $this->config = new AppConfig();
        $this->config->load();


        $this->initLogger();
        $this->initDI();


        $this->profiler->profilerFinish('Application:beforeRun');

    }

    public function run(): void
    {
        $this->profiler->profilerStart('Application:run');

        $this->beforeRun();

        try {
            /** @var Router $router */
            $router = $this->diContainer->get(Router::class);

            /** @var Resolver $resolver */
            $resolver = $this->diContainer->get(Resolver::class);

            $this->profiler->profilerStart('Application:initializeRouter');
            $router->initialize();

            $pageRoutes = $router->getPageRoutes();
            $resolver->setPageRoutes($pageRoutes);

            $this->profiler->profilerFinish('Application:initializeRouter');

            $response = $router->execute();

            $response->sendHeaders()->sendContent();
        } catch (\Throwable $ex) {
            $this->logger->error('Unable to run application', ['ex' => $ex]);

            throw $ex;
        } finally {
            $this->profiler->profilerFinish('Application:run');
            if ($this->logger !== null && $this->config->getMode() === ApplicationMode::DEVELOP) {
                $this->profiler->output($this->logger);

            }
        }
    }

    final protected function getContainer(): ContainerInterface
    {
        return $this->diContainer;
    }

    final protected function getConfig(): AppConfig
    {
        return $this->config;
    }
}
