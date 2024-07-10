<?php

declare(strict_types=1);

namespace Liquid\Blog\Block;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Core\Model\BlockContext;

class PostList extends TemplateBlock
{
    protected string|null $template = 'Liquid_Blog::post/list.phtml';

    private array $posts = [];

    public function __construct(BlockContext $context, TemplateHelper $templateHelper, private readonly BlogRepository $blogRepository)
    {
        parent::__construct($context, $templateHelper);
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
