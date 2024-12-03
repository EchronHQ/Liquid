<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

readonly class Context
{
    public function __construct(
        private Request         $request,
        private Response        $response,
        private ResultFactory   $resultFactory,
        private SegmentConfig   $configuration,
        private LoggerInterface $logger
    )
    {
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

    public function getConfiguration(): SegmentConfig
    {
        return $this->configuration;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
