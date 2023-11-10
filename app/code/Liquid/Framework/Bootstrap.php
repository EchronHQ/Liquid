<?php
declare(strict_types=1);

use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Client;
use Attlaz\Model\Log\LogStreamId;
use DI\ContainerBuilder;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\ApplicationMode;
use Monolog\ErrorHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;


class Bootstrap
{
    private $objectManager;

    private LoggerInterface|null $logger = null;
    private AppConfig $config;

    private readonly Profiler $profiler;

    public function __construct(ContainerBuilder $factory, string $rootDir, array $initParams = [])
    {
//        $this->factory = $factory;
//        $this->rootDir = $rootDir;
//        $this->server = $initParams;
//        $this->objectManager = $this->factory->create($this->server);
    }

    public static function create(string $rootDir, array $initParams = []): Bootstrap
    {

        $factory = self::createObjectManagerFactory($rootDir);

        return new self($factory, $rootDir, $initParams);
    }

    public static function createObjectManagerFactory(string $rootDir): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
//        $containerBuilder->enableCompilation(ROOT . 'var/cache');

        // $cachePool = $this->buildCachePool();
        $containerBuilder->addDefinitions([
//            LoggerInterface::class => $this->logger,
//            SqlRemoteService::class => $this->buildSQL(),
//            AppConfig::class => $this->config,
////            CacheItemPoolInterface::class => $cachePool,
//            Profiler::class => $this->profiler,
//            ComponentRegistrarInterface::class => \DI\create(ComponentRegistrar::class),
//            DirectoryList::class => new \Liquid\Framework\Filesystem\DirectoryList($rootDir),
        ]);
//        $this->diContainer = $containerBuilder->build();
//        $dirList = new \Liquid\Framework\Filesystem\DirectoryList($rootDir);

        return $containerBuilder;
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
}
