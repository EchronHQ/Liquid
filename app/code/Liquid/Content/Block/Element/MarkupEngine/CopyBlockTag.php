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
    public const PROP_ICON = 'icon';
    public const PROP_ICON_STYLE = 'icon-style';
//    public const PROP_CLASSES = 'classes';

    public const PROPERTIES = [
        self::PROP_TITLE,
        self::PROP_TYPES,
        self::PROP_TITLE_TAG,
        self::PROP_ICON,
        self::PROP_ICON_STYLE,
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

    private function validateData(): void
    {
        $dataKeys = $this->getDataKeys();
        foreach ($dataKeys as $dataKey) {
            if ($dataKey !== 'content' && !in_array($dataKey, self::PROPERTIES, true)) {
                $this->logger->warning('ContentBlockTag: Unknown property `' . $dataKey . '` set', ['value' => $this->getData($dataKey)]);
            }
        }
    }

    public function toHtml(): string
    {
        $this->validateData();

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

        if ($this->hasData(self::PROP_ICON)) {
            $style = $this->getData(self::PROP_ICON_STYLE);
            if ($style === null) {
                $style = 'round size-50 back-pink front-white';
            }
            $block->setHeaderIcon($this->getData(self::PROP_ICON), $style);
        }

        $block->setContent($this->getContent());


        return $block->toHtml();
    }
}
