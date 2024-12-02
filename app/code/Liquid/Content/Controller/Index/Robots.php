<?php
declare(strict_types=1);

namespace Liquid\Content\Controller\Index;

use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\ResultInterface;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\LayoutPage;

#[Route('robots.txt', name: 'robots')]
class Robots extends AbstractAction
{
    public function __construct(
        Context                                 $context,
        private readonly Layout                 $layout,
        private readonly PageConfig             $pageConfig,
        private readonly PageRepository         $pageRepository,
        private readonly ObjectManagerInterface $objectManager,
    )
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $result = $this->getResultFactory()->create(LayoutPage::class);

        /** @var LayoutPage $resultPage */
//        $resultPage = $this->objectManager->create(LayoutPage::class);
//        $resultPage->addHandle('robots_index_index');
        $result->setHeader('Content-Type', 'text/plain');
        $result->addHandle('robots_index_index');
        return $result;
    }
}
