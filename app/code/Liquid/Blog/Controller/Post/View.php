<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Post;

use Liquid\Blog\Block\Post;
use Liquid\Blog\Block\RelatedBlogPostsSection;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Block\Element\DemoCallToActionBlock;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class View extends FrontendAction
{
    /**
     * blog/post/view/post/why-data-connectivity
     * blog/why-data-connectivity
     */


    public function __construct(
        Context                         $context,
        Layout                          $layout,
        PageConfig                      $pageConfig,
        private readonly BlogRepository $blogRepository
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): Result|null
    {
        $articlePage = $this->getArticleByRequest();
        if (\is_null($articlePage)) {
            $this->logger->error('Unable to show blog post, post not found', ['request' => $this->getRequest()->getParams()]);
            return null;

        }
        return $this->renderPage($articlePage);
    }


    private function getArticleByRequest(): PostDefinition|null
    {
        $postIdentifier = $this->getRequest()->getParam('postId');
        if (\is_null($postIdentifier)) {
            return null;
        }
        return $this->blogRepository->getByUrlKey($postIdentifier);
    }


    private function renderPage(AbstractViewableEntity $articlePage): Result
    {


        $this->layout->runHandle('layout-1col');
        //        $this->layout->runHandle('layout-2col-left');


        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('blog-post');
        $this->pageConfig->addBodyClass('blog-post-' . $articlePage->id);

        PageConfigHelper::append($articlePage, $this->pageConfig);

        /**
         * Content
         */
        $pageBlock = $this->layout->addBlock(Post::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::post/view.phtml');
        $pageBlock->setData('post', $articlePage);

        /**
         * Content
         */
        $contentBlock = $this->layout->createBlock(Post::class, 'blog-content');
        $contentBlock->setTemplate($articlePage->template);
        $contentBlock->setData('post', $articlePage);

        $pageBlock->setChild('content', $contentBlock);

        /**
         * Related posts
         * @var RelatedBlogPostsSection $resourcesBlock
         */
        $resourcesBlock = $this->layout->addBlock(RelatedBlogPostsSection::class, 'resources', 'content');
        $resourcesBlock->setTitle('Related Posts');

        /**
         * Explore more
         */
        //        $exploreBlock = $this->layout->addBlock(Post::class, 'explore-more', 'content');
        //        $exploreBlock->setTemplate('Liquid_Blog::explore-more.phtml');
        //        $exploreBlock->setData('post', $articlePage);

        /**
         * Demo
         * @var DemoCallToActionBlock $callToAction
         */
        $callToAction = $this->layout->addBlock(DemoCallToActionBlock::class, 'call-to-action', 'content');
        $callToAction->setDescription("Let's talk about what Attlaz can do for your business.");
        return $this->getResultFactory()->create(Page::class);
    }


}
