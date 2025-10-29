<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\ViewModel\CategoryViewViewModel;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

#[Route('blog/category/view/category-id/:category-id', name: 'blog-category-view')]
class Category extends FrontendAction
{
    /**
     * http://localhost:8080/blog/category/product
     */
    public function __construct(
        Context                                 $context,
        Layout                                  $layout,
        PageConfig                              $pageConfig,
        private readonly BlogRepository         $blogRepository,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): AbstractResult
    {

        $categoryIdentifier = $this->getRequest()->getParam('category-id');
        $category = $this->blogRepository->getCategoryById($categoryIdentifier);
        if ($category === null) {
            $this->logger->error('Unable to render blog category, category not found', ['params' => $this->getRequest()->getParams()]);
            throw new NotFoundException('Page not found');
        }
        return $this->renderPage($category);
    }


    private function renderPage(CategoryDefinition $categoryDefinition): AbstractResult
    {

        $result = $this->getResultFactory()->create(Page::class);

        $this->layout->runHandle('layout-1col');

//        $this->pageConfig->addBodyClass('theme-mustard');
        $this->pageConfig->addBodyClass('blog-category');
        $this->pageConfig->addBodyClass('blog-category-' . $categoryDefinition->id);

        PageConfigHelper::append($categoryDefinition, $this->pageConfig);

        $categoryViewViewModel = $this->objectManager->create(CategoryViewViewModel::class);
        $categoryViewViewModel->setCategory($categoryDefinition);

        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::category/view.phtml');
        $pageBlock->setViewModel($categoryViewViewModel);
        $pageBlock->setViewModel($this->objectManager->create(BaseViewModel::class), 'base');

        return $result;
    }
}
