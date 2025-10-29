<?php

declare(strict_types=1);

namespace Liquid\Content\ViewModel;

use Liquid\Blog\Model\TermDefinition;
use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Framework\View\Element\ArgumentInterface;

class TagBar implements ArgumentInterface
{

    private AbstractViewableEntity|null $current = null;
    private array $tags = [];
    private string|null $label = null;
    private string|null $allTarget = null;

    /**
     * @return AbstractViewableEntity[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param AbstractViewableEntity[] $tags
     * @return void
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function isSelectedTag(AbstractViewableEntity $tag): bool
    {
        if ($this->current === null) {
            return false;
        }
        return $this->current->id === $tag->id;
    }

    public function hasSelected(): bool
    {
        return $this->current !== null;
    }

    public function setCurrent(AbstractViewableEntity|null $current): void
    {
        $this->current = $current;
    }

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getAllTarget(): string|null
    {
        return $this->allTarget;
    }

    public function setAllTarget(string $target): void
    {
        $this->allTarget = $target;
    }

    public function getTagLabel(AbstractViewableEntity $tag): string
    {
        if ($tag instanceof TermDefinition) {
            return $tag->termLong;
        }
        return $tag->metaTitle;
    }
}
