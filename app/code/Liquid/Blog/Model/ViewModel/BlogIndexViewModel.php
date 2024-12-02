<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Framework\View\Element\ArgumentInterface;
use Psr\Log\LoggerInterface;

class BlogIndexViewModel implements ArgumentInterface
{
    /** @var CategoryDefinition[]|null */
    private array|null $categories = null;

    /**
     * @var TagDefinition[]|null
     */
    private array|null $tags = null;

    public function __construct(
        private readonly BlogRepository  $blogRepository,
        private readonly LoggerInterface $logger,
    )
    {

    }

    /**
     * @return PostDefinition[]
     */
    public function getPosts(): array
    {
        return $this->blogRepository->getPosts();
    }

    /**
     * @param CategoryDefinition $category
     * @param int $limit
     * @return PostDefinition[]
     */
    public function getLastCategoryPosts(CategoryDefinition $category, int $limit): array
    {
        return $this->blogRepository->getPostsByCategoryId($category->id, $limit);
    }


//    public function getCategoryTags(PostDefinition $postDefinition): array
//    {
//        $this->loadCategories();
//        $postCategories = [];
//        foreach ($postDefinition->categoryIds as $categoryId) {
//            $category = $this->getCategoryById($categoryId);
//            if (\is_null($category)) {
//                $this->logger->warning('Category "' . $categoryId . '" not found for blog post "' . $postDefinition->id . '"');
//            } else {
//                $postCategories[] = $category;
//            }
//        }
//        return $postCategories;
//
//
//    }

    public function getCategories(): array
    {
        $this->loadCategories();
        return $this->categories;
    }

    public function getTags(): array
    {
        $this->loadTags();
        return $this->tags;
    }

    private function loadCategories(): void
    {
        if (\is_null($this->categories)) {
            $this->categories = $this->blogRepository->getCategories();
        }
    }

    private function loadTags(): void
    {
        if (\is_null($this->tags)) {
            $this->tags = $this->blogRepository->getTags();
        }
    }
}
