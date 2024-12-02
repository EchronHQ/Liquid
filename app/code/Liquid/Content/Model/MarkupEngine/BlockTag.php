<?php

declare(strict_types=1);

namespace Liquid\Content\Model\MarkupEngine;

use Liquid\Framework\View\Element\BlockInterface;

class BlockTag extends CustomTag
{
    private BlockInterface|null $_block = null;

    public function render(): string
    {
        if ($this->_block !== null) {

            // var_dump($this->innermarkers);
            $this->_block->setData('content', $this->contentHtml);
            foreach ($this->attributes as $attribute => $attributeValue) {
                $this->_block->setData($attribute, $attributeValue);
            }
            $output = $this->_block->toHtml();

            if ($output === '') {
                // $output = '[' . get_class($this->_block) . ']';
            }
            return $output;
        }

        // TODO: log that we are unable to render this tag
        return '[Child block not defined for tag]';
    }

    public function setXBlock(BlockInterface $block): void
    {
        $this->_block = $block;
    }

    public function getXBlock(): BlockInterface
    {
        return $this->_block;
    }
}
