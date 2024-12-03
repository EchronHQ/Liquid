<?php

declare(strict_types=1);

namespace Liquid\Admin\Controller\Admin\Noroute;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

#[Route('noroute/index', name: 'admin-noroute-view', routerId: 'admin')]
class Index implements ActionInterface
{
    public function __construct(
        Context                                 $context,
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }

    public function execute(): AbstractResult
    {

        die('No route found (admin)');
//        /** @var \Liquid\Backend\Model\View\Result\Page $resultPage */
//        $resultPage = $this->resultPageFactory->create();
//        $resultPage->setStatusHeader(404, '1.1', 'Not Found');
//        $resultPage->setHeader('Status', '404 File not found');
//        $resultPage->addHandle('adminhtml_noroute');
//        return $resultPage;
    }


}
