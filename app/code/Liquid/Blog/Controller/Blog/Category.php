<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class Category extends FrontendAction
{
    /**
     * http://localhost:8080/blog/category/product
     */
    public function __construct(
        Context                         $context,
        Layout                          $layout,
        PageConfig                      $pageConfig,
        private readonly BlogRepository $blogRepository
    ) {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): ?Result
    {

        $categoryIdentifier = $this->getRequest()->getParam('categoryId');
        $category = $this->blogRepository->getCategoryByUrlKey($categoryIdentifier);
        if (\is_null($category)) {
            $this->logger->error('Unable to render blog category, category not found', ['params' => $this->getRequest()->getParams()]);
            return null;
        }
        return $this->renderPage($category);
    }


    private function renderPage(CategoryDefinition $categoryDefinition): Result
    {


        $this->layout->runHandle('layout-1col');

        $this->pageConfig->addBodyClass('theme-aqua');
        $this->pageConfig->addBodyClass('blog-category');
        $this->pageConfig->addBodyClass('blog-category-' . $categoryDefinition->id);

        PageConfigHelper::append($categoryDefinition, $this->pageConfig);

        $pageBlock = $this->layout->addBlock(\Liquid\Blog\Block\Category::class, 'page', 'content');
        $pageBlock->setTemplate('category/view.phtml');
        $pageBlock->setData('category', $categoryDefinition);

        return $this->getResultFactory()->create(Page::class);
    }
}
