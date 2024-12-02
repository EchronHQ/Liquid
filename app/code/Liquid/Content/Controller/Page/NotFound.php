<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Page;

use Laminas\Http\Response as ResponseAlias;
use Liquid\Content\Block\HtmlHeadBlock;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Core\Router;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

class NotFound extends AbstractAction
{
    public function __construct(
        Context                         $context,
        private readonly Layout         $layout,
        private readonly PageConfig     $pageConfig,
        private readonly PageRepository $pageRepository
    )
    {
        parent::__construct($context);
    }

    public function execute(): Result
    {


        $page = $this->pageRepository->getById(Router::PAGE_NOT_FOUND_IDENTIFIER);
        if (\is_null($page)) {
            $this->logger->warning('No page found with identifier', ['identifier' => Router::PAGE_NOT_FOUND_IDENTIFIER]);
            throw new NotFoundException('Page not found');
        }

        return $this->renderPage($page);
    }

    private function renderPage(PageDefinition $page): Result
    {
        $this->layout->runHandle('layout-1col');

        /** @var HtmlHeadBlock $headBlock */
        $headBlock = $this->layout->getBlock('head');


        $this->pageConfig->addBodyClass('page-' . $page->id);

        PageConfigHelper::append($page, $this->pageConfig);

        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate($page->template);


        $pageBlock->setData('page', $page);


        $pageInstance = $this->getResultFactory()->create(Page::class);
        $pageInstance->setHttpResponseCode(ResponseAlias::STATUS_CODE_404);
        return $pageInstance;
    }
}
