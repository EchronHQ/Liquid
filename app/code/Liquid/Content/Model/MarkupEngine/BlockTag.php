<?php

declare(strict_types=1);

namespace Liquid\Content\Model\MarkupEngine;

use Liquid\Core\Model\Layout\AbstractBlock;

class BlockTag extends CustomTag
{
    private AbstractBlock|null $_block = null;

    public function render(): string
    {
        if ($this->_block !== null) {

            // var_dump($this->innermarkers);
            $this->_block->setData('content', $this->contentHtml);
            foreach ($this->attributes as $attribute => $attributeValue) {
                $this->_block->setData($attribute, $attributeValue);
            }
            return $this->_block->toHtml();
        }

        // TODO: log that we are unable to render this tag
        return '';
    }

    public function setXBlock(AbstractBlock $block): void
    {
        $this->_block = $block;
    }
}
