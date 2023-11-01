<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Blog\Model\TermDefinition;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Model\Resource\PageDefinition;

class TagBar extends TemplateBlock
{
    protected string|null $template = 'Liquid_Content::element/tagbar.phtml';


    private PageDefinition|null $current = null;
    private array $tags = [];
    private string|null $label = null;
    private string|null $allTarget = null;

    /**
     * @return PageDefinition[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param PageDefinition[] $tags
     * @return void
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function isSelectedTag(PageDefinition $tag): bool
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

    public function setCurrent(PageDefinition $current): void
    {
        $this->current = $current;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function setAllTarget(string $target): void
    {
        $this->allTarget = $target;
    }

    public function getAllTarget(): string|null
    {
        return $this->allTarget;
    }

    public function getTagLabel(PageDefinition $tag): string
    {
        if ($tag instanceof TermDefinition) {
            return $tag->termLong;
        }
        return $tag->metaTitle;
    }
}
