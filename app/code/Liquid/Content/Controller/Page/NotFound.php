<?php

declare(strict_types=1);

namespace Liquid\Content\Controller\Page;

use Laminas\Http\Response as ResponseAlias;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\Controller\AbstractResult;
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

    public function execute(): AbstractResult
    {
        // TODO: get this from config
        $pageId = '';

        $page = $this->pageRepository->getById($pageId);
        if ($page === null) {
            $this->logger->warning('No page found with identifier', ['identifier' => $pageId]);
            throw new NotFoundException('Page not found');
        }

        return $this->renderPage($page);
    }

    private function renderPage(PageDefinition $page): AbstractResult
    {
        $this->layout->runHandle('layout-1col');


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
