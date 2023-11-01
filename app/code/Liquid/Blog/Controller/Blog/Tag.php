<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class Tag extends FrontendAction
{
    /**
     * http://localhost:8080/blog/tag/product
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

        $tagIdentifier = $this->getRequest()->getParam('tagId');
        $tag = $this->blogRepository->getTagByUrlKey($tagIdentifier);
        if (\is_null($tag)) {
            $this->logger->error('Unable to render blog tag, tag not found', ['params' => $this->getRequest()->getParams()]);
            return null;
        }
        return $this->renderPage($tag);
    }


    private function renderPage(TagDefinition $categoryDefinition): Result
    {


        $this->layout->runHandle('layout-1col');

        $this->pageConfig->addBodyClass('theme-aqua');
        $this->pageConfig->addBodyClass('blog-tag');
        $this->pageConfig->addBodyClass('blog-tag-' . $categoryDefinition->id);

        PageConfigHelper::append($categoryDefinition, $this->pageConfig);

        $pageBlock = $this->layout->addBlock(\Liquid\Blog\Block\Tag::class, 'page', 'content');
        $pageBlock->setTemplate('tag/view.phtml');
        $pageBlock->setData('tag', $categoryDefinition);

        return $this->getResultFactory()->create(Page::class);
    }
}
