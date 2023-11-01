<?php

declare(strict_types=1);

namespace Liquid\Blog\Block;

use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;

class Tag extends Posts
{
    public function getTag(): TagDefinition
    {
        $tag = $this->getData('tag');
        if (\is_null($tag)) {
            throw new \Exception('Tag must be defined');
        }
        return $tag;
    }

    /**
     * @return PostDefinition[]
     * @throws \Exception
     */
    public function getPosts(): array
    {
        return $this->blogRepository->getPostsByTagId($this->getTag()->id);
    }
}
