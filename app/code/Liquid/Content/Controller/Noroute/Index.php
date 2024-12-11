<?php
declare(strict_types=1);

namespace Liquid\Content\Controller\Noroute;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Controller\Result\Plain;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

#[Route('content/noroute/index', name: 'no_route')]
class Index implements ActionInterface
{


    public function __construct(

        private readonly Request                $request,
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
//        /** @var Result\Forward $resultForward */
//        $resultForward = $this->objectManager->create(Result\Forward::class);
//        $resultForward->setController('index');
//        $resultForward->forward('defaultNoRoute');
//        return $resultForward;
    }


}

