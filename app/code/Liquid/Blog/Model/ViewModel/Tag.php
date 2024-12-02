<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Blog\Repository\BlogRepository;
use Liquid\Framework\View\Element\ArgumentInterface;

class Tag implements ArgumentInterface
{
    private TagDefinition|null $tag = null;

    public function __construct(
        private readonly BlogRepository $blogRepository
    )
    {

    }

    public function getTag(): TagDefinition
    {

        if (null === $this->tag) {
            throw new \RuntimeException('Tag must be defined');
        }
        return $this->tag;
    }

    public function setTag(TagDefinition $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return PostDefinition[]
     * @throws \Exception
     */
    public function getPosts(): array
    {
        return $this->blogRepository->getPostsByTagId($this->getTag()->id);
    }

    /**
     * @return TagDefinition[]
     */
    public function getTags(): array
    {
        return $this->blogRepository->getTags();
    }
}
