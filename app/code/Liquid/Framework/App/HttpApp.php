<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\App\Response\ResponseInterface;
use Liquid\Framework\ObjectManager\ConfigLoader;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class HttpApp implements AppInterface
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly ConfigLoader           $configLoader,
        private readonly AreaList               $areaList,
        private readonly State                  $state,
        private readonly Request                $request,
        private readonly Response               $response
    )
    {

    }

    public function launch(): ResponseInterface
    {
        $areaCode = $this->areaList->getCodeByFrontName($this->request->getFrontName());
        $this->state->setAreaCode($areaCode);
        $this->objectManager->configure($this->configLoader->load($areaCode));

        $frontController = $this->objectManager->get(FrontController::class);

        $result = $frontController->dispatch($this->request);
        $result->renderResult($this->response);
        if ($this->request->isHead() && $this->response->getHttpResponseCode() === 200) {
            $this->handleHeadRequest();
        }
        return $this->response;
    }

    /**
     * Handle HEAD requests by adding the Content-Length header and removing the body from the response.
     *
     * @return void
     */
    private function handleHeadRequest(): void
    {
        // It is possible that some PHP installations have overloaded strlen to use mb_strlen instead.
        // This means strlen might return the actual number of characters in a non-ascii string instead
        // of the number of bytes. Use mb_strlen explicitly with a single byte character encoding to ensure
        // that the content length is calculated in bytes.
        $contentLength = mb_strlen($this->response->getContent(), '8bit');
        $this->response->clearBody();
        $this->response->setHeader('Content-Length', (string)$contentLength);
    }
}
