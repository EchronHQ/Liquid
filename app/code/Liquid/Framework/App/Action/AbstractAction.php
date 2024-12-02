<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Core\Model\AppConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\Controller\ResultFactory;
use Liquid\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements ActionInterface
{
    protected readonly LoggerInterface $logger;
    protected readonly Request $request;
    protected readonly Response $response;
    private readonly ResultFactory $resultFactory;
    private readonly AppConfig $configuration;

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

    abstract public function execute(): ResultInterface;
}
