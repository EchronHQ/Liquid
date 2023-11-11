<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;

use Liquid\Content\Block\Element\CopyBlock;
use Liquid\Core\Model\Layout\Block;

class CopyBlockTag extends Block
{
    public const PROP_TITLE = 'title';
    public const PROP_TYPES = 'types';
    public const PROP_TITLE_TAG = 'title-tag';
//    public const PROP_CLASSES = 'classes';

    public const PROPERTIES = [
        self::PROP_TITLE,
        self::PROP_TYPES,
        self::PROP_TITLE_TAG,
//        self::PROP_CLASSES,
    ];


    public function getTypes(): string|null
    {
        return $this->getData(self::PROP_TYPES);
    }

    public function getContent(): string|null
    {
        $content = $this->getData('content');
        if ($content === null || $content === '') {
            //            $this->logger->warning('[Copy Block] No content defined');
            $content = null;
        }
        return $content;
    }

    public function toHtml(): string
    {
        $data = [];

        $types = $this->getTypes();
        if ($types !== null) {
            $data['types'] = $types;
        }

        $block = $this->getLayout()->createBlock(CopyBlock::class, '', $data);

        if ($this->hasData(self::PROP_TITLE)) {
            $title = $this->getData(self::PROP_TITLE);
            $titleTag = $this->getData(self::PROP_TITLE_TAG);
            if ($titleTag === null) {
                $block->setHeaderTitle($title);
            } else {
                $block->setHeaderTitle($title, $titleTag);
            }
        }

        $block->setContent($this->getContent());


        return $block->toHtml();
    }
}
