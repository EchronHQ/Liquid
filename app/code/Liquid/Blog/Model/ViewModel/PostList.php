<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Framework\View\Element\ArgumentInterface;
use Psr\Log\LoggerInterface;

class PostList implements ArgumentInterface
{
    private array $posts = [];

    public function __construct(
        private readonly BlogRepository  $blogRepository,
        private readonly LoggerInterface $logger
    )
    {

    }

    /**
     * @return PostDefinition[]
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    public function setPosts(array $posts): void
    {
        $this->posts = $posts;
    }

    public function getCategory(PostDefinition $post): CategoryDefinition|null
    {

        if ($post->categoryId === null) {
            return null;
        }
        $category = $this->blogRepository->getCategoryById($post->categoryId);
        if ($category !== null) {
            return $category;
        }
        $this->logger->warning('Blog category with id "' . $post->categoryId . '" for post "' . $post->id . '" not found');
        return null;
    }

    /**
     * @param PostDefinition $post
     * @return TagDefinition[]
     */
    public function getTags(PostDefinition $post): array
    {
        $tagIds = $post->tagIds;

        $result = [];
        foreach ($tagIds as $tagId) {
            $tag = $this->blogRepository->getTagById($tagId);
            if ($tag === null) {
                $this->logger->warning('Blog category with id "' . $tagId . '" for post "' . $post->id . '" not found');
            } else {
                // TODO: do we need to sort the tags?
                $result[] = $tag;
            }
        }
        return $result;
    }

    public function estimateReadingTime(PostDefinition $post): int
    {
        return $this->blogRepository->estimateReadingTime($post);
    }

}
