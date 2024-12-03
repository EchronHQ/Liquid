<?php

declare(strict_types=1);

namespace Liquid\Core;

use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\AppInterface;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\App\State;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\ObjectManager\ObjectManagerFactory;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\Output\Error;
use Psr\Log\LoggerInterface;

class Application
{
    private ObjectManagerInterface $objectManager;
    private LoggerInterface|null $logger = null;
//    private SegmentConfig $config;

    private readonly Profiler $profiler;

    public function __construct(
        ObjectManagerFactory    $factory,
        private readonly string $rootDir,
        private readonly array  $initParams
    )
    {
        $this->objectManager = $factory->create($initParams);
        $this->profiler = new Profiler();
    }

    /**
     * Static method so that client code does not have to create Object Manager Factory every time Bootstrap is called
     *
     * @param string $rootDir
     * @param array $initParams
     * @param ObjectManagerFactory|null $factory
     * @return Application
     */
    public static function create(
        string               $rootDir,
        array                $initParams,
        ObjectManagerFactory $factory = null
    ): Application
    {
        // self::populateAutoloader($rootDir, $initParams);
        if ($factory === null) {
            $factory = self::createObjectManagerFactory($rootDir, $initParams);
        }
        return new self($factory, $rootDir, $initParams);
    }

    /**
     * Creates instance of object manager factory
     *
     * @param string $rootDir
     * @param array $initParams
     * @return ObjectManagerFactory
     */
    public static function createObjectManagerFactory(string $rootDir, array $initParams): ObjectManagerFactory
    {
        $dirList = new DirectoryList($rootDir);
//        $dirList = self::createFilesystemDirectoryList($rootDir, $initParams);
//        $driverPool = self::createFilesystemDriverPool($initParams);
//        $configFilePool = self::createConfigFilePool();
        return new ObjectManagerFactory($dirList);
    }

    /**
     * Gets the current parameters
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->initParams;
    }




//    final protected function beforeRun(): void
//    {
//        $this->profiler->profilerStart('Application:beforeRun');
//
//        $this->config = new AppConfig();
//
//
//        $x = new DirectoryList($this->rootDir);
//        $this->config->load($x->getPath(Path::CONFIG));
//
//
//        $this->initLogger();
//        $this->initDI();
//
//
//        $this->profiler->profilerFinish('Application:beforeRun');
//
//    }

    public function run(AppInterface $application): void
    {
        ini_set('memory_limit', '2048M');
        $this->profiler->profilerStart('Application:run');


        try {

            $response = $application->launch();
            $response->sendResponse();


//            $this->beforeRun();
//            /** @var Router $router */
//            $router = $this->diContainer->get(Router::class);
//
//            /** @var Resolver $resolver */
//            $resolver = $this->diContainer->get(Resolver::class);
//
//            $this->profiler->profilerStart('Application:initializeRouter');
//            $router->initialize();
//
//            $pageRoutes = $router->getPageRoutes();
//            $resolver->setPageRoutes($pageRoutes);
//
//            $this->profiler->profilerFinish('Application:initializeRouter');
//
//            $response = $router->execute();
//
//            $response->sendHeaders()->sendContent();
        } catch (\Throwable $ex) {
            $this->terminate($ex);


            if ($this->logger !== null) {
                $this->logger->error('Unable to run application', ['ex' => $ex]);
            }


            // Safe to file?
            if ($this->isDeveloperMode()) {


                echo Error::toHtml($ex);
                http_response_code(500);
                exit(1);
//                die('xxx');
//                return;
            }

            // TODO: show error code + log error to file with same code
            throw $ex;


            //throw $ex;
        } finally {
            $this->profiler->profilerFinish('Application:run');
            if ($this->logger !== null && $this->isDeveloperMode()) {
                $this->profiler->output($this->logger);

            }
        }
    }

    /**
     * Checks whether developer mode is set in the initialization parameters
     *
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        $mode = AppMode::Develop->value;
        if (isset($this->server[State::PARAM_MODE])) {
            $mode = $this->server[State::PARAM_MODE];
        } else {
            $appConfig = $this->objectManager->get(\Liquid\Framework\App\Config\SegmentConfig::class);
            $configMode = $appConfig->getValue(State::PARAM_MODE);
            if ($configMode) {
                $mode = $configMode;
            }
        }
        return $mode === AppMode::Develop->value;
    }

    public function createApplication(string $type, array $arguments = []): AppInterface|null
    {
        try {
            $application = $this->objectManager->create($type, $arguments);
            if (!($application instanceof AppInterface)) {
                throw new \InvalidArgumentException("The provided class doesn't implement AppInterface: {$type}");
            }
            return $application;
        } catch (\Exception $e) {
            $this->terminate($e);
        }
        return null;
    }

    /**
     * Display an exception and terminate program execution
     *
     * @param \Throwable $e
     * @return void
     */
    protected function terminate(\Throwable $e): void
    {
        /** @var Response $response */
        $response = $this->objectManager->get(Response::class);
        $response->clearHeaders();
        $response->setHttpResponseCode(500);
        $response->setHeader('Content-Type', 'text/plain');
        if ($this->isDeveloperMode()) {
            // TODO: use helper to format error to html
            $response->setBody('<pre>' . (string)$e . '<pre>');
        } else {
            $message = "An error has happened during application run. See exception log for details.\n";
            try {
                if (!$this->objectManager) {
                    throw new \DomainException();
                }
                $this->objectManager->get(LoggerInterface::class)->critical($e);
            } catch (\Exception $e) {
                $message .= "Could not write error message to log. Please use developer mode to see the message.\n";
            }
            $response->setBody($message);
        }
        $response->sendResponse();
        exit(1);
    }

    final protected function getContainer(): ObjectManagerInterface
    {
        return $this->objectManager;
    }

//    final protected function getConfig(): SegmentConfig
//    {
//        return $this->config;
//    }
}
