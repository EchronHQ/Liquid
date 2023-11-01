<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Core\Model\Layout\AbstractBlock;

class RowBlock extends AbstractBlock
{
    /** @var AbstractBlock[] */
    private array $children = [];

    public function toHtml(): string
    {
        $output = '<div class="row">';

        foreach ($this->children as $child) {
            $output .= $child->toHtml();
        }
        $output .= '</div>';

        return $output;
    }

    public function addChild(AbstractBlock $block): void
    {
        $this->children[] = $block;
    }


}
