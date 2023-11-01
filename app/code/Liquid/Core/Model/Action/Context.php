<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Action;

use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\Request\Request;
use Liquid\Core\Model\Request\Response;
use Liquid\Core\Model\Result\ResultFactory;
use Psr\Log\LoggerInterface;

readonly class Context
{
    public function __construct(
        private Request         $request,
        private Response        $response,
        private ResultFactory   $resultFactory,
        private AppConfig       $configuration,
        private LoggerInterface $logger
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getResultFactory(): ResultFactory
    {
        return $this->resultFactory;
    }

    public function getConfiguration(): AppConfig
    {
        return $this->configuration;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
