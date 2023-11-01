<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Block\Posts;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class Overview extends FrontendAction
{
    public function __construct(Context $context, Layout $layout, private readonly BlogRepository $blogRepository, PageConfig $pageConfig)
    {
        parent::__construct($context, $layout, $pageConfig);
    }

    public function execute(): Result|null
    {
        $page = $this->blogRepository->getPageById('blog');
        if ($page === null) {
            $this->logger->error('Unable to show blog overview, page `blog` not found');
            return null;
        }
        return $this->renderPage($page);
    }


    private function renderPage(PageDefinition $articlePage): Result
    {

        $this->layout->runHandle('layout-1col');

        //        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('blog');

        PageConfigHelper::append($articlePage, $this->pageConfig);

        /** @var Posts $overviewBlock */
        $overviewBlock = $this->layout->addBlock(Posts::class, 'posts', 'content');
        $overviewBlock->setTemplate('Liquid_Blog::blog.phtml');
        $overviewBlock->setData('page', $articlePage);

        return $this->getResultFactory()->create(Page::class);
    }
}
