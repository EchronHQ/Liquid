<?php

declare(strict_types=1);

namespace Liquid\Core\Controller;

use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\PageRepository;
use Liquid\Core\Router;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

/**
 * @deprecated
 */
class PageNotFoundController extends FrontendAction
{
    public function __construct(
        Context                         $context,
        Layout                          $layout,
        PageConfig                      $pageConfig,
        private readonly PageRepository $pageRepository
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }

    public function execute(): Result
    {
        //        if ($this->getConfiguration()->getMode() === ApplicationMode::DEVELOP) {
        $info = [
            'path' => $this->getRequest()->getPathInfo(),
            'params' => $this->getRequest()->getParams(),
            'headers' => $this->getRequest()->getHeaders(),
        ];
        $this->logger->warning('Page not found', $info);
        //        }
        $page = $this->pageRepository->getById(Router::PAGE_NOT_FOUND_IDENTIFIER);
        if ($page === null) {
            throw new NotFoundException('Page not found page definition not found');
        }
        return $this->renderPage($page);
    }

    private function renderPage(PageDefinition $page): Result
    {
        $this->layout->runHandle('layout-1col');


        return $this->getResultFactory()->create(Page::class);
    }
}
