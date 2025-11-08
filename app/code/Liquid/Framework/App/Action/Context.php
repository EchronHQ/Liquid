<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Request\HttpRequest;
use Liquid\Framework\App\Response\HttpResponse;
use Liquid\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

readonly class Context
{
    public function __construct(
        private HttpRequest     $request,
        private HttpResponse    $response,
        private ResultFactory   $resultFactory,
        private ScopeConfig     $configuration,
        private LoggerInterface $logger
    )
    {
    }

    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    public function getResponse(): HttpResponse
    {
        return $this->response;
    }

    public function getResultFactory(): ResultFactory
    {
        return $this->resultFactory;
    }

    public function getConfiguration(): ScopeConfig
    {
        return $this->configuration;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
