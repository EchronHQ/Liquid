<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Framework\View\Element\ArgumentInterface;

class RelatedBlogPostsSection implements ArgumentInterface
{
    public string $title = 'Continue Reading';

    public function __construct(private readonly BlogRepository $blogRepository)
    {
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

    public function filterByCategory(string $category): void
    {

    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
