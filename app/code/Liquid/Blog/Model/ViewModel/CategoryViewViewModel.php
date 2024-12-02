<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Framework\View\Element\ArgumentInterface;

class CategoryViewViewModel implements ArgumentInterface
{
    private CategoryDefinition|null $category = null;

    public function __construct(
        private readonly BlogRepository $blogRepository
    )
    {
    }

    public function getCategory(): CategoryDefinition
    {

        if ($this->category === null) {
            throw new \Exception('Category must be defined');
        }
        return $this->category;
    }

    public function setCategory(CategoryDefinition $category): void
    {
        $this->category = $category;
    }

    /**
     * @return PostDefinition[]
     * @throws \Exception
     */
    public function getPosts(): array
    {

        return $this->blogRepository->getPostsByCategoryId($this->getCategory()->id);
    }

    /**
     * @return CategoryDefinition[]
     */
    public function getCategories(): array
    {
        return $this->blogRepository->getCategories();
    }


}
