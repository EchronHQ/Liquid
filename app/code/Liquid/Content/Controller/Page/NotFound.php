<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Page;

use Liquid\Content\Block\HtmlHeadBlock;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\AbstractAction;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;
use Liquid\Core\Router;
use Laminas\Http\Response as ResponseAlias;

class NotFound extends AbstractAction
{
    public function __construct(
        Context                         $context,
        private readonly Layout         $layout,
        private readonly PageConfig     $pageConfig,
        private readonly PageRepository $pageRepository
    ) {
        parent::__construct($context);
    }


    private function renderPage(PageDefinition $page): Result
    {
        $this->layout->runHandle('layout-1col');

        /** @var HtmlHeadBlock $headBlock */
        $headBlock = $this->layout->getBlock('head');


        $this->pageConfig->addBodyClass('page-' . $page->id);

        PageConfigHelper::append($page, $this->pageConfig);

        $pageBlock = $this->layout->addBlock(TemplateBlock::class, 'page', 'content');
        $pageBlock->setTemplate($page->template);


        $pageBlock->setData('page', $page);


        $pageInstance = $this->getResultFactory()->create(Page::class);
        $pageInstance->setHttpResponseCode(ResponseAlias::STATUS_CODE_404);
        return $pageInstance;
    }

    public function execute(): Result|null
    {


        $page = $this->pageRepository->getById(Router::PAGE_NOT_FOUND_IDENTIFIER);
        if (\is_null($page)) {
            $this->logger->warning('No page found with identifier', ['identifier' => Router::PAGE_NOT_FOUND_IDENTIFIER]);
            //            return null;
        }

        return $this->renderPage($page);
    }
}
