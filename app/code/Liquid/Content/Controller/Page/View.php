<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Page;

use Liquid\Content\Helper\PageHelper;
use Liquid\Content\Repository\PageRepository;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Exception\NotFoundException;

#[Route('content/page/view/page-id/:page-id', name: 'page-view')]
class View extends AbstractAction
{
    public function __construct(
        Context                         $context,
        private readonly PageRepository $pageRepository,
        private readonly PageHelper     $pageHelper
    )
    {
        parent::__construct($context);
    }

    public function execute(): AbstractResult
    {
        $pageIdentifier = null;
        if ($this->getRequest()->getParam('page-id') !== null) {
            $pageIdentifier = $this->getRequest()->getParam('page-id');
        }

        if ($pageIdentifier === null) {
            throw new NotFoundException('Page not found');
        }

        $page = $this->pageRepository->getById($pageIdentifier);

        if ($page === null) {
            $this->logger->warning('No page found with identifier', ['identifier' => $pageIdentifier]);
            throw new NotFoundException('Page not found');
        }

        return $this->pageHelper->prepareResultPage($page);
    }

//    private function renderPage(PageDefinition $page): AbstractResult
//    {
//        $result = $this->getResultFactory()->create(Page::class);
//
//        $this->layout->runHandle('layout-1col');
//
//        /** @var Template $headBlock */
//        $headBlock = $this->layout->getBlock('head');
//        /** @var \Liquid\Content\ViewModel\HtmlHead $headViewModel */
//        $headViewModel = $headBlock->getViewModel('', \Liquid\Content\ViewModel\HtmlHead::class);
//
//        if ($page->id === 'contact') {
//            $headViewModel->addScript(new Script('js/contact.js'));
//        }
//        if ($page->id === 'demo') {
//            $headViewModel->addScript(new Script('js/demo.js'));
//        }
//
//
//        $this->pageConfig->addBodyClass('page-' . $page->id);
//
//        PageConfigHelper::append($page, $this->pageConfig);
//
//        $baseViewModel = $this->objectManager->get(BaseViewModel::class);
//        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
//        $pageBlock->setViewModel($baseViewModel)->setTemplate($page->template);
//        $pageBlock->setData('page', $page);
//
//
//        return $result;
//    }
}
