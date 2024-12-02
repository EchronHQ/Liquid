<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;

use Liquid\Content\Block\Element\CopyBlock;
use Liquid\Framework\View\Element\Template;

class CopyBlockTag extends Template
{
    public const PROP_TITLE = 'title';
    public const PROP_CAPTION = 'caption';
    public const PROP_TYPES = 'types';
    public const PROP_TITLE_TAG = 'title-tag';
    public const PROP_ICON = 'icon';
    public const PROP_ICON_STYLE = 'icon-style';
//    public const PROP_CLASSES = 'classes';

    public const PROPERTIES = [
        self::PROP_TITLE,
        self::PROP_CAPTION,
        self::PROP_TYPES,
        self::PROP_TITLE_TAG,
        self::PROP_ICON,
        self::PROP_ICON_STYLE,
//        self::PROP_CLASSES,
    ];

    public function toHtml(): string
    {
        $this->validateData();

        $data = [];

        $types = $this->getTypes();
        if ($types !== null) {
            $data['types'] = $types;
        }

        /** @var CopyBlock $block */
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
        if ($this->hasData(self::PROP_CAPTION)) {
            $block->setHeaderCaption($this->getData(self::PROP_CAPTION));
        }

        if ($this->hasData(self::PROP_ICON)) {
            $style = $this->getData(self::PROP_ICON_STYLE);
            if ($style === null) {
                // $style = 'round size-50 back-pink front-white';
            }
            $block->setHeaderIcon($this->getData(self::PROP_ICON), $style);
        }

        $block->setContent($this->getContent());


        return $block->toHtml();
    }

    private function validateData(): void
    {
        $dataKeys = $this->getDataKeys();
        foreach ($dataKeys as $dataKey) {
            if ($dataKey !== 'content' && !in_array($dataKey, self::PROPERTIES, true)) {
                $ex = new \Exception('');
                $this->logger->warning('Copy Block Tag: Unknown property `' . $dataKey . '` set', ['value' => $this->getData($dataKey), 'info' => $this->getNameInLayout(), 'call stack' => $ex->getTraceAsString()]);
            }
        }
    }

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
}
