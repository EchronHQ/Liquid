<?php

declare(strict_types=1);

namespace Liquid\Core;

use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\AppInterface;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Response\HttpResponse;
use Liquid\Framework\App\Response\HttpResponseCode;
use Liquid\Framework\App\State;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\ObjectManager\ObjectManagerFactory;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Application
{
    /**
     * Possible errors that can be triggered by the bootstrap
     */
    public const int ERR_MAINTENANCE = 901;
    public const int ERR_IS_INSTALLED = 902;

    private ObjectManagerInterface $objectManager;
    private LoggerInterface|null $logger = null;
//    private SegmentConfig $config;

    private readonly Profiler $profiler;
    /**
     * Bootstrap-specific error code that may have been set in runtime
     *
     * @var int
     */
    private int $errorCode = 0;

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
        string                    $rootDir,
        array                     $initParams,
        ObjectManagerFactory|null $factory = null
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

    /**
     * Gets the current parameters
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->initParams;
    }

    public function run(AppInterface $application): void
    {
        \ini_set('memory_limit', '2048M');


        try {
            try {
                $this->profiler->profilerStart('Application:run');
                $response = $application->launch();
                $response->sendResponse();
                $this->profiler->profilerFinish('Application:run');
            } catch (\Throwable $e) {
                $this->profiler->profilerFinish('Application:run');
                if (!$application->catchException($this, $e)) {
                    throw $e;
                }


//                if ($this->logger !== null) {
//                    $this->logger->error('Unable to run application', ['ex' => $ex]);
//                }
//
//
//                // Safe to file?
//                if ($this->isDeveloperMode()) {
//
//
//                    echo Error::toHtml($ex);
//                    \http_response_code(500);
//                    exit(1);
////                die('xxx');
////                return;
//                }
//
//                // TODO: show error code + log error to file with same code
//                throw $ex;


                //throw $ex;
            } finally {

                if ($this->logger !== null && $this->isDeveloperMode()) {
                    $this->profiler->output($this->logger);

                }
            }
        } catch (\Throwable $e) {
            $this->terminate($e);
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
            $appConfig = $this->objectManager->get(ScopeConfig::class);
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
        } catch (\Throwable $e) {
            $this->terminate($e);
        }
        return null;
    }

    /**
     * Getter for error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Display an exception and terminate program execution
     *
     * @param \Throwable $e
     * @return void
     */
    protected function terminate(\Throwable $e): void
    {
        /** @var HttpResponse $response */
        $response = $this->objectManager->get(HttpResponse::class);
        $response->clearHeaders();
        $response->setHttpResponseCode(HttpResponseCode::STATUS_CODE_500);
        $response->setHeader('Content-Type', 'text/plain');
        if ($this->isDeveloperMode()) {
            // TODO: use helper to format error to html
            $response->setBody('<pre>' . (string)$e . '<pre>');
        } else {
            // TODO: create nicer
            $message = "An error has happened during application run. See exception log for details.\n";
            try {
                if (!$this->objectManager) {
                    throw new \DomainException();
                }
                $this->objectManager->get(LoggerInterface::class)->critical($e);
            } catch (\Exception $e) {
                $message .= "Could not write error message to log. Please use developer mode to see the message.\n";
            }

            // TODO: use some kind of template rendering?
            // TODO: this should only happen in the http app

            //   $body = \file_get_contents($this->rootDir . '/vendor/echron/liquid/app/code/Liquid/Framework/Exception/templates/error.phtml');
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
