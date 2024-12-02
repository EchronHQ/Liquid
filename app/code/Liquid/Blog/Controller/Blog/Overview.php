<?php
declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Model\ViewModel\BlogIndexViewModel;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

#[Route('blog/page/view/page-id/:page-id', name: 'blog-index')]
class Overview extends FrontendAction
{
    public function __construct(
        Context                                 $context,
        Layout                                  $layout,
        private readonly BlogRepository         $blogRepository,
        PageConfig                              $pageConfig,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }

    public function execute(): Result
    {
        $page = $this->blogRepository->getPageById('blog');
        if ($page === null) {
            $this->logger->error('Unable to show blog overview, page `blog` not found');
            throw new NotFoundException('Page not found');
        }
        return $this->renderPage($page);
    }


    private function renderPage(PageDefinition $articlePage): Result
    {
        $result = $this->getResultFactory()->create(Page::class);
        $this->layout->runHandle('layout-1col');

        //        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('blog');

        PageConfigHelper::append($articlePage, $this->pageConfig);


        $overviewBlock = $this->layout->addBlock(Template::class, 'posts', 'content');
        $overviewBlock->setTemplate('Liquid_Blog::blog.phtml');

        $blogIndexViewModel = $this->objectManager->create(BlogIndexViewModel::class);

        $overviewBlock->setViewModel($blogIndexViewModel);
        $overviewBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');
        $overviewBlock->setData('page', $articlePage);

        return $result;
    }
}
