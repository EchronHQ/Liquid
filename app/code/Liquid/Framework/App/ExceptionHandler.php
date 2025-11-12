<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Core\Application;
use Liquid\Framework\App\Request\HttpRequest;
use Liquid\Framework\App\Response\HttpResponse;
use Liquid\Framework\Encryption\Encryptor;
use Liquid\Framework\Exception\ErrorProcessor;
use Liquid\Framework\Exception\InitException;
use Liquid\Framework\Exception\SessionException;
use Psr\Log\LoggerInterface;

class ExceptionHandler
{
    public function __construct(
        private readonly Encryptor       $encryptor,
        private readonly ErrorProcessor  $errorProcessor,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * Handles exception of HTTP web application
     *
     * @param Application $bootstrap
     * @param \Throwable $exception
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return bool
     */
    public function handle(
        Application  $bootstrap,
        \Throwable   $exception,
        HttpResponse $response,
        HttpRequest  $request
    ): bool
    {
        return $this->handleDeveloperMode($bootstrap, $exception, $response)
            || $this->handleBootstrapErrors($bootstrap, $exception, $response)
            || $this->handleSessionException($exception, $response, $request)
            || $this->handleInitException($exception, $response)
            || $this->handleGenericReport($bootstrap, $exception, $response);
    }

    /**
     * Error handler for developer mode
     *
     * @param Application $bootstrap
     * @param \Exception $exception
     * @param HttpResponse $response
     * @return bool
     */
    private function handleDeveloperMode(
        Application  $bootstrap,
        \Throwable   $exception,
        HttpResponse $response
    ): bool
    {
        if ($bootstrap->isDeveloperMode()) {
            if (Application::ERR_IS_INSTALLED === $bootstrap->getErrorCode()) {
                try {
                    $this->redirectToSetup($bootstrap, $exception, $response);
                    return true;
                } catch (\Exception $e) {
                    $exception = $e;
                }
            }


            $response->clearHeader('Location');


            // TODO: improve the developer error output (show stacktrace, error message, envionrment variables, etc)
            $this->errorProcessor->processError($response, 500, $exception->getMessage() . '<br/>' . $exception->getTraceAsString());


            return true;
        }
        return false;
    }


    /**
     * Handler for bootstrap errors
     *
     * @param Application $bootstrap
     * @param \Throwable $exception
     * @param HttpResponse $response
     * @return bool
     */
    private function handleBootstrapErrors(
        Application  $bootstrap,
        \Throwable   &$exception,
        HttpResponse $response
    ): bool
    {
        $bootstrapCode = $bootstrap->getErrorCode();
        if (Application::ERR_MAINTENANCE === $bootstrapCode) {

            $this->errorProcessor->processError($response, 503, 'Maintenance mode is enabled');
            return true;
        }
        if (Application::ERR_IS_INSTALLED === $bootstrapCode) {
            try {
                $this->redirectToSetup($bootstrap, $exception, $response);
                return true;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        return false;
    }

    /**
     * Handler for session errors
     *
     * @param \Throwable $exception
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return bool
     */
    private function handleSessionException(
        \Throwable   $exception,
        HttpResponse $response,
        HttpRequest  $request
    ): bool
    {
        if ($exception instanceof SessionException) {
            $response->setRedirect($request->getDistroBaseUrl());
            $response->sendHeaders();
            return true;
        }
        return false;
    }

    /**
     * Handler for application initialization errors
     *
     * @param \Throwable $exception
     * @return bool
     */
    private function handleInitException(\Throwable $exception, HttpResponse $response): bool
    {
        if ($exception instanceof InitException) {
            $this->logger->critical($exception);
            $this->errorProcessor->processError($response, 404, $exception->getMessage());
            return true;
        }
        return false;
    }

    /**
     * Handle for any other errors
     *
     * @param Application $bootstrap
     * @param \Throwable $exception
     * @return bool
     */
    private function handleGenericReport(Application $bootstrap, \Throwable $exception, HttpResponse $response): bool
    {
        $reportData = [
            $exception->getMessage(),
//            Debug::trace(
//                $exception->getTrace(),
//                true,
//                false,
//                (bool)getenv('LQ_DEBUG_SHOW_ARGS')
//            ),
        ];
        $params = $bootstrap->getParams();
        if (isset($params['REQUEST_URI'])) {
            $reportData['url'] = $params['REQUEST_URI'];
        }
        if (isset($params['SCRIPT_NAME'])) {
            $reportData['script_name'] = $params['SCRIPT_NAME'];
        }
        $reportData['report_id'] = $this->encryptor->getHash(\implode('', $reportData));
        $this->logger->critical($exception, ['report_id' => $reportData['report_id']]);

        $this->errorProcessor->processErrorReport($response, $reportData);

        return true;
    }

    /**
     * If not installed, try to redirect to installation wizard
     *
     * @param Application $bootstrap
     * @param \Throwable $exception
     * @param HttpResponse $response
     * @return void
     * @throws \Exception
     */
    private function redirectToSetup(Application $bootstrap, \Throwable $exception, HttpResponse $response): void
    {
        $newMessage = $exception->getMessage() . "\nNOTE: Liquid is not installed" . "\n";

        throw new \Exception($newMessage, 0, $exception);
    }
}
