<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Framework\DataObject;
use Liquid\Framework\View\Element\ArgumentInterface;
use Psr\Log\LoggerInterface;

class PostViewModel extends DataObject implements ArgumentInterface
{
    /** @var CategoryDefinition[]|null */
    private array|null $categories = null;

    /**
     * @var TagDefinition[]|null
     */
    private array|null $tags = null;
    private string|null $postContent = null;

    public function __construct(
        private readonly BlogRepository  $blogRepository,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    public function getPostCategory(): CategoryDefinition|null
    {
        $this->loadCategories();

        $postDefinition = $this->getPost();


        if ($postDefinition->categoryId === null) {
            return null;
        }

        $category = $this->getCategoryById($postDefinition->categoryId);
        if (\is_null($category)) {
            $this->logger->warning('Category "' . $postDefinition->categoryId . '" not found for blog post "' . $postDefinition->id . '"');
            return null;
        }

        return $category;


    }

    public function getCategories(): array
    {
        $this->loadCategories();
        return $this->categories;
    }

    public function getPost(): PostDefinition
    {
        return $this->getData('post');
    }

    /**
     * @return TagDefinition[]
     */
    public function getPostTags(): array
    {
        $this->loadTags();

        $postDefinition = $this->getPost();
        $postTags = [];
        foreach ($postDefinition->tagIds as $tagId) {
            $tag = $this->getTagById($tagId);
            if ($tag === null) {
                $this->logger->warning('Post "' . $tagId . '" not found for blog post "' . $postDefinition->id . '"');
            } else {
                $postTags[] = $tag;
            }
        }
        return $postTags;


    }

    public function getPostAnchors(): array
    {
        $postDefinition = $this->getPost();
        if ($postDefinition->anchors === null) {
            /**
             * Detect anchors automatically (doesn't work with copy-blocks)
             */
            $matchCount = \preg_match_all("/<h.*?id=\"([^\"]*)\".*?>([^<]*)<\/h[^>]+>/", $this->getPostContent(), $headings);


            $titles = [];
            for ($i = 0; $i < $matchCount; $i++) {
                $tag = $headings[0][$i];
                $id = $headings[1][$i];
                $title = $headings[2][$i];

                $titles[] = ['target' => '#' . $id, 'label' => $title];
            }
            return $titles;
        }
        return $postDefinition->anchors;
    }

    public function getPostContent(): string
    {
        return $this->postContent;
    }

    public function setPostContent(string $postBlock): void
    {
        $this->postContent = $postBlock;
    }

    private function loadCategories(): void
    {
        if (\is_null($this->categories)) {
            $this->categories = $this->blogRepository->getCategories();
        }
    }

    private function getCategoryById(string|int $id): CategoryDefinition|null
    {
        $this->loadCategories();
        foreach ($this->categories as $category) {
            if ($category->id === $id) {
                return $category;
            }
        }
        return null;
    }

    private function loadTags(): void
    {
        if (\is_null($this->tags)) {
            $this->tags = $this->blogRepository->getTags();
        }
    }

    private function getTagById(string|int $id): TagDefinition|null
    {
        $this->loadTags();
        foreach ($this->tags as $tag) {
            if ($tag->id === $id) {
                return $tag;
            }
        }
        return null;
    }
}
