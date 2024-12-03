<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Post;

use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\ViewModel\PostViewModel;
use Liquid\Blog\Model\ViewModel\RelatedBlogPostsSection;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Block\Element\DemoCallToActionBlock;
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

#[Route('blog/post/view/post-id/:post-id', name: 'blog-post-view')]
class View extends FrontendAction
{
    /**
     * blog/post/view/post/why-data-connectivity
     * blog/why-data-connectivity
     */


    public function __construct(
        Context                                 $context,
        Layout                                  $layout,
        PageConfig                              $pageConfig,
        private readonly BlogRepository         $blogRepository,
        private readonly ObjectManagerInterface $objectManager,
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): AbstractResult
    {
        $articlePage = $this->getArticleByRequest();
        if (\is_null($articlePage)) {
            $this->logger->error('Unable to show blog post, post not found', ['request' => $this->getRequest()->getParams()]);
            throw new NotFoundException('Page not found');

        }
        return $this->renderPage($articlePage);
    }


    private function getArticleByRequest(): PostDefinition|null
    {
        $postIdentifier = $this->getRequest()->getParam('post-id');
        if (\is_null($postIdentifier)) {
            return null;
        }
        return $this->blogRepository->getByUrlKey($postIdentifier);
    }


    private function renderPage(PostDefinition $articlePage): AbstractResult
    {

        $result = $this->getResultFactory()->create(Page::class);
        $this->layout->runHandle('layout-1col');
        //        $this->layout->runHandle('layout-2col-left');


        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('blog-post');
        $this->pageConfig->addBodyClass('blog-post-' . $articlePage->id);

        PageConfigHelper::append($articlePage, $this->pageConfig);

        /**
         * Content
         */
        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::post/view.phtml');
        $pageBlock->setData('post', $articlePage);

        $pageBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');


        $blogPostViewModel = $this->objectManager->get(PostViewModel::class);
        $blogPostViewModel->setData('post', $articlePage);
        $pageBlock->setViewModel($blogPostViewModel);
        /**
         * Content
         */
        $contentBlock = $this->layout->createBlock(Template::class, 'blog-content');
        $contentBlock->setTemplate($articlePage->template);

        $contentBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');
        $contentBlock->setData('post', $articlePage);

        $blogPostViewModel->setPostContent($contentBlock->toHtml());


        // $pageBlock->setChild('content', $contentBlock);

        /**
         * Related posts
         */
        $relatedBlogPosts = $this->layout->createBlock(Template::class, 'related-posts', ['data' => ['template' => 'Liquid_Blog::related-posts-section.phtml']]);
        $this->layout->setChild('content', 'related-posts');

        $relatedBlogPostsViewModel = $this->objectManager->create(RelatedBlogPostsSection::class);
        $relatedBlogPostsViewModel->setTitle('Related Posts');

        $relatedBlogPosts->setViewModel($relatedBlogPostsViewModel);
        $relatedBlogPosts->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');


        /**
         * Explore more
         */
        //        $exploreBlock = $this->layout->addBlock(Post::class, 'explore-more', 'content');
        //        $exploreBlock->setTemplate('Liquid_Blog::explore-more.phtml');
        //        $exploreBlock->setData('post', $articlePage);

        /**
         * Add call to action block
         */
        $callToAction = $this->layout->addBlock(Template::class, 'call-to-action', 'content');

        $viewModel = $this->objectManager->create(DemoCallToActionBlock::class);
        $viewModel->setDescription("Let's talk about what Attlaz can do for your business.");

        $callToAction->setViewModel($viewModel);

        return $result;
    }


}
