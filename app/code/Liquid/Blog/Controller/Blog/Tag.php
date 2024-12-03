<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Blog;

use Liquid\Blog\Model\TagDefinition;
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

#[Route('blog/tag/view/tag-id/:tag-id', name: 'blog-tag-view')]
class Tag extends FrontendAction
{
    /**
     * http://localhost:8080/blog/tag/product
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

        $tagIdentifier = $this->getRequest()->getParam('tag-id');
        $tag = $this->blogRepository->getTagById($tagIdentifier);
        if (\is_null($tag)) {
            $this->logger->error('Unable to render blog tag, tag not found', ['params' => $this->getRequest()->getParams()]);
            throw new NotFoundException('Page not found');
        }
        return $this->renderPage($tag);
    }


    private function renderPage(TagDefinition $categoryDefinition): AbstractResult
    {

        $result = $this->getResultFactory()->create(Page::class);

        $this->layout->runHandle('layout-1col');

        $this->pageConfig->addBodyClass('theme-aqua');
        $this->pageConfig->addBodyClass('blog-tag');
        $this->pageConfig->addBodyClass('blog-tag-' . $categoryDefinition->id);

        PageConfigHelper::append($categoryDefinition, $this->pageConfig);

        $tagViewModel = $this->objectManager->create(\Liquid\Blog\Model\ViewModel\Tag::class);
        $tagViewModel->setTag($categoryDefinition);

        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::tag/view.phtml');

        $pageBlock->setViewModel($tagViewModel);
        $pageBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');

        return $result;
    }
}
