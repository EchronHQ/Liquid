<?php
declare(strict_types=1);

namespace Liquid\Content\Controller\Noroute;

use Liquid\Content\Helper\PageHelper;
use Liquid\Content\Repository\PageRepository;
use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Controller\Result\Plain;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

#[Route('content/noroute/index', name: 'no_route')]
class Index implements ActionInterface
{


    public function __construct(

        private readonly Request                $request,
        private readonly PageHelper             $pageHelper,
        private readonly LoggerInterface        $logger,
        private readonly ObjectManagerInterface $objectManager
    )
    {


    }

//    private function validateReCaptcha(string $token): float
//    {
//        return 0;
//    }

    public function execute(): AbstractResult
    {

        $this->logPageNotFound();
        /** @var PageRepository $pageHelper */
        $pageHelper = $this->objectManager->get(PageRepository::class);

        $page = $pageHelper->getById('not-found');
        if ($page !== null) {
            $resultPage = $this->pageHelper->prepareResultPage($page);
            $resultPage->setStatusHeader(404, '1.1', 'Not Found');
            $resultPage->setHeader('Status', '404 File not found');
            $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            return $resultPage;
        }

        // $resultPage = $pageHelper->prepareResultPage($this, $pageId);


        //        /** @var Result\Forward $resultForward */
//        $resultForward = $this->objectManager->create(Forward::class);
//        $resultForward->setController('index');
//        $resultForward->forward('defaultNoRoute');
//        return $resultForward;

        //$pageId = null;


//        echo '<div>Page not found</div>';
//        echo '<div>Request: ' . $this->request->getUri() . '</div>';

        // TODO: can we show all registered routes?

        $resultPage = $this->objectManager->create(Plain::class);
        $resultPage->setText('<div>Page not found</div><div>Request: ' . $this->request->getUri() . '</div>');
        //     if ($resultPage) {
        $resultPage->setStatusHeader(404, '1.1', 'Not Found');
        $resultPage->setHeader('Status', '404 File not found');
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

        return $resultPage;
//        }
//
//

    }

    private function logPageNotFound(): void
    {
        $ignoreUrls = [
            '/.well-known/appspecific/com.chrome.devtools.json',
        ];
        if (\in_array($this->request->getRequestUri(), $ignoreUrls)) {
            return;
        }
        // TODO: filter certain unknown urls
        $this->logger->critical('Page not found', [
            'Path info' => $this->request->getRequestUri(),
        ]);
    }


}

