<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Admin\Content;

use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout as Layout2;
use Liquid\Framework\View\Result\Page;

#[Route('layout', name: 'admin-layout')]
class Layout extends AbstractAction
{
    public function __construct(
        Context                                 $context,
        private readonly Layout2                $layout,
        private readonly PageConfig             $pageConfig,
        private readonly PageRepository         $pageRepository,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context);
    }

    public function execute(): Result
    {


        $page = $this->pageRepository->getById('home');
        if (\is_null($page)) {
            $this->logger->warning('No page found with identifier', ['identifier' => 'page-not-found']);
            //            return null;
            throw new NotFoundException('Page not found');
        }

        return $this->renderPage($page);
    }

    private function renderPage(PageDefinition $page): Result
    {
        $result = $this->getResultFactory()->create(Page::class);

        $this->layout->runHandle('layout-1col');


        //   $headBlock = $this->layout->getBlock('head');


        $this->pageConfig->addBodyClass('page-' . $page->id);

        PageConfigHelper::append($page, $this->pageConfig);
        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('theme--light');
        $this->pageConfig->addBodyClass('palette--chroma');
        $this->pageConfig->addBodyClass('accent--blurple');

        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Content::page/layout/layout.phtml');
        $pageBlock->setViewModel($this->objectManager->create(BaseViewModel::class), 'base');
        $pageBlock->setData('page', $page);


//        $pageInstance = $this->getResultFactory()->create(Page::class);
//        $pageInstance->setHttpResponseCode(ResponseAlias::STATUS_CODE_404);
        return $result;
    }
}
