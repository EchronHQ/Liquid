<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Page;

use Liquid\Content\Block\Html\Script;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

#[Route('content/page/view/page-id/:page-id', name: 'page-view')]
class View extends AbstractAction
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

    public function execute(): AbstractResult
    {
        $pageIdentifier = null;
        if (!\is_null($this->getRequest()->getParam('page-id'))) {
            $pageIdentifier = $this->getRequest()->getParam('page-id');
        }

        if (\is_null($pageIdentifier)) {
            throw new NotFoundException('Page not found');
        }

        $page = $this->pageRepository->getById($pageIdentifier);

        if (\is_null($page)) {
            $this->logger->warning('No page found with identifier', ['identifier' => $pageIdentifier]);
            throw new NotFoundException('Page not found');
        }

        return $this->renderPage($page);
    }

    private function renderPage(PageDefinition $page): AbstractResult
    {
        $result = $this->getResultFactory()->create(Page::class);

        $this->layout->runHandle('layout-1col');

        /** @var Template $headBlock */
        $headBlock = $this->layout->getBlock('head');
        /** @var \Liquid\Content\ViewModel\HtmlHead $headViewModel */
        $headViewModel = $headBlock->getViewModel('', \Liquid\Content\ViewModel\HtmlHead::class);

        if ($page->id === 'contact') {
            $headViewModel->addScript(new Script('js/contact.js'));
        }
        if ($page->id === 'demo') {
            $headViewModel->addScript(new Script('js/demo.js'));
        }


        $this->pageConfig->addBodyClass('page-' . $page->id);

        PageConfigHelper::append($page, $this->pageConfig);

        $baseViewModel = $this->objectManager->get(BaseViewModel::class);
        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setViewModel($baseViewModel)->setTemplate($page->template);
        $pageBlock->setData('page', $page);


        return $result;
    }
}
