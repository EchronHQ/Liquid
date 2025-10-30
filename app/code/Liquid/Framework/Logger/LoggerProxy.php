<?php
declare(strict_types=1);

namespace Liquid\Framework\Logger;

use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Client;
use Attlaz\Model\Log\LogStreamId;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Monolog\ErrorHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

class LoggerProxy implements LoggerInterface
{
    private LoggerInterface|null $logger = null;

    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->emergency($message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->alert($message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->critical($message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->error($message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->warning($message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->notice($message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->info($message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->debug($message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $context = $this->addExceptionToContext($message, $context);
        $this->getLogger()->log($level, $message, $context);
    }

    /**
     * Ensure exception logging by adding it to context
     * TODO: maybe we can solve this with a handler?
     *
     * @param mixed $message
     * @param array $context
     * @return array
     */
    protected function addExceptionToContext(\Throwable|string $message, array $context = []): array
    {
        if ($message instanceof \Throwable && !isset($context['exception'])) {
            $context['exception'] = $message;
        }
        return $context;
    }

    private function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {

            $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
            $this->logger = new Logger('Attlaz Site');

            ErrorHandler::register($this->logger);


            /**
             * Slack handler
             */
            if (true):
                // TODO: read this from config (deployment config is not working!!!)
                $slackHook = $deploymentConfig->getValue('logging/slack/webhook', null);
                $slackChannel = $deploymentConfig->getValue('logging/slack/channel', null);
                $slackUsername = $deploymentConfig->getValue('logging/slack/username', 'Liquid');
                $slackMinLogLevel = $deploymentConfig->getValue('logging/slack/minloglevel', Level::Info->name);

                // TODO: validate config, if not complete, don't enable
                $slackHandler = new SlackWebhookHandler($slackHook, $slackChannel, $slackUsername, true, null, false, true);
                $slackHandler->setLevel($slackMinLogLevel);
                if ($this->enableDebug()) {
                    $this->logger->pushHandler($slackHandler);
                }
                //                $this->logger->error('Run tests');
                //                return;

            endif;
            //  if (!$this->appConfig->isCLI() && $deploymentConfig->getValueMode() === AppMode::Develop) {

            $browserMinLogLevel = $deploymentConfig->getValue('logging/browser/minloglevel', Level::Error->name);

            //  var_dump($browserMinLogLevel);
            $browserConsoleHandler = new BrowserConsoleHandler();
            $browserConsoleHandler->setLevel($browserMinLogLevel);
            $this->logger->pushHandler($browserConsoleHandler);
            //     }

            //        $cliHandler = new StreamHandler(fopen('php://stdout', 'wb'), Level::Debug);
            //        $htmlFormatter = new HtmlFormatter();
            //        $cliHandler->setFormatter($htmlFormatter);
            //        $this->logger->pushHandler($cliHandler);


            $attlazLogStreamId = $deploymentConfig->getValue('logging/attlaz/logstream_id', '');
            if ($attlazLogStreamId !== '') {

                $client = new Client();

                $attlazClientToken = $deploymentConfig->getValue('logging/attlaz/client_token', '');
                if ($attlazClientToken === '') {
                    $attlazClientId = $deploymentConfig->getValue('logging/attlaz/client_id');
                    $attlazClientSecret = $deploymentConfig->getValue('logging/attlaz/client_secret');
                    $client->authWithClient($attlazClientId, $attlazClientSecret);
                } else {
                    $client->authWithToken($attlazClientToken);
                }


                $attlazApiEndpoint = $deploymentConfig->getValue('logging/attlaz/endpoint');

                $attlazMinLogLevel = $deploymentConfig->getValue('logging/attlaz/minloglevel', Level::Info->name);


                $client->setEndPoint($attlazApiEndpoint);
                $attlazHandler = new AttlazHandler($client, new LogStreamId($attlazLogStreamId));
                $attlazHandler->setLevel($attlazMinLogLevel);
                $this->logger->pushHandler($attlazHandler);
            }


            if ($this->isCLI()) {
                //Stream handler
                $cliHandler = new StreamHandler(\STDOUT, Level::Debug);
                $this->logger->pushHandler($cliHandler);
            } elseif ($deploymentConfig->getValue('mode') === AppMode::Production) {

                $webProcessor = new WebProcessor();
                $this->logger->pushProcessor($webProcessor);
            }
            $webProcessor = new WebProcessor();
            $this->logger->pushProcessor($webProcessor);
        }
        return $this->logger;
    }

    private function enableDebug(): bool
    {
        return true;
    }

    private function isCLI(): bool
    {
        return false;
    }
}
