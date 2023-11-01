<?php

declare(strict_types=1);

namespace Liquid\Blog\Block;

use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;

class Category extends Posts
{
    public function getCategory(): CategoryDefinition
    {
        $category = $this->getData('category');
        if (\is_null($category)) {
            throw new \Exception('Category must be defined');
        }
        return $category;
    }

    /**
     * @return PostDefinition[]
     * @throws \Exception
     */
    public function getPosts(): array
    {

        return $this->blogRepository->getPostsByCategoryId($this->getCategory()->id);
    }


}
