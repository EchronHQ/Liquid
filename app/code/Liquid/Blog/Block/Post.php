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

class Post extends TemplateBlock
{
    /** @var CategoryDefinition[]|null */
    private array|null $categories = null;

    /**
     * @var TagDefinition[]|null
     */
    private array|null $tags = null;

    public function __construct(BlockContext $context, TemplateHelper $templateHelper, protected BlogRepository $blogRepository)
    {
        parent::__construct($context, $templateHelper);
    }


    public function getPost(): PostDefinition
    {
        return $this->getData('post');
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

    public function getCategories(): array
    {
        $this->loadCategories();
        return $this->categories;
    }

    private string|null $postContent = null;

    public function getTitles(): array
    {


        $matchCount = preg_match_all("/<h.*?id=\"([^\"]*)\".*?>([^<]*)<\/h[^>]+>/", $this->getPostContent(), $headings);


        $titles = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $tag = $headings[0][$i];
            $id = $headings[1][$i];
            $title = $headings[2][$i];

            $titles[] = ['target' => '#' . $id, 'label' => $title];
        }
        return $titles;
    }

    public function getPostContent(): string
    {
        if ($this->postContent === null) {
            $contentBlock = $this->getChildBlock('content');
            if ($contentBlock !== null) {
                $this->postContent = $contentBlock->toHtml();
            }

        }
        return $this->postContent;
    }
}
