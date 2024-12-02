<?php
declare(strict_types=1);

namespace Liquid\Content\Controller\Noroute;

use Liquid\Content\Repository\FormRepository;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Result\LayoutPage;

#[Route('content/noroute/index', name: 'no_route')]
class Index extends AbstractAction
{


    public function __construct(
        Context                                 $context,
        private readonly FormRepository         $formRepository,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context);


    }

//    private function validateReCaptcha(string $token): float
//    {
//        return 0;
//    }


    public function execute(): Result
    {
        /** @var LayoutPage|null $resultPage */
        //   $resultPage = null;// $pageHelper->prepareResultPage($this, $pageId);


        echo '<div>Page not found</div>';
        echo $this->request->getUri();

        // TODO: can we show all registered routes?

        $resultPage = $this->objectManager->create(LayoutPage::class);
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

