<?php

declare(strict_types=1);

namespace Liquid\Blog\Block;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Model\BlockContext;

class RelatedBlogPostsSection extends TemplateBlock
{
    protected string|null $template = 'Liquid_Blog::related-posts-section.phtml';

    public string $title = 'Continue Reading';

    public function __construct(
        BlockContext                    $context,
        TemplateHelper                  $templateHelper,
        private readonly BlogRepository $blogRepository
    )
    {
        parent::__construct($context, $templateHelper);
    }

    /**
     * @return PageDefinition[]
     */
    public function getBlogPosts(): array
    {
        $result = [];
        $blogPosts = $this->blogRepository->getPosts();
        $result = \array_merge($result, $blogPosts);
        shuffle($result);
        return \array_slice($result, 0, 4);
    }

    public function filterByCategory(string $category):void{

    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
