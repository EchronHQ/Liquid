<?php

declare(strict_types=1);

namespace Liquid\Content\Block;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Model\BlockContext;

class RelatedBlogPostsBlock extends TemplateBlock
{
    protected string|null $template = 'Liquid_Content::block/blogposts.phtml';

    public string $title = 'Continue Reading';

    public function __construct(
        BlockContext $context,
        TemplateHelper $templateHelper,
        private readonly BlogRepository $blogRepository
    ) {
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
        return \array_slice($result, 0, 3);
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
