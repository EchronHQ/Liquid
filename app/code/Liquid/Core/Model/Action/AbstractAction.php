<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Action;

use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\Request\Request;
use Liquid\Core\Model\Request\Response;
use Liquid\Core\Model\Result\Result;
use Liquid\Core\Model\Result\ResultFactory;
use Psr\Log\LoggerInterface;

abstract class AbstractAction
{
    private readonly Request $request;
    private readonly Response $response;
    private readonly ResultFactory $resultFactory;
    private readonly AppConfig $configuration;
    protected readonly LoggerInterface $logger;

    public function __construct(Context $context)
    {
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->resultFactory = $context->getResultFactory();
        $this->configuration = $context->getConfiguration();
        $this->logger = $context->getLogger();
    }

    protected function getRequest(): Request
    {
        return $this->request;
    }

    protected function getResponse(): Response
    {
        return $this->response;
    }

    protected function getResultFactory(): ResultFactory
    {
        return $this->resultFactory;
    }

    protected function getConfiguration(): AppConfig
    {
        return $this->configuration;
    }

    abstract public function execute(): Result|null;
}
