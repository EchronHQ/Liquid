<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;

use Liquid\Content\Block\TemplateBlock;

class PageSectionTag extends TemplateBlock
{
    public const PROP_MAX_WIDTH = 'max-width';
    public const PROP_BACKGROUND = 'background';
    public const PROP_ARCH_TOP = 'arch-top';
    public const PROP_ARCH_BOTTOM = 'arch-bottom';
    public const PROP_TYPES = 'types';

    public const PROPERTIES = [
        self::PROP_MAX_WIDTH,
        self::PROP_BACKGROUND,
        self::PROP_ARCH_TOP,
        self::PROP_ARCH_BOTTOM,
        self::PROP_TYPES,
    ];


    public function getMaxWidth(): string|null
    {
        $maxWidth = $this->getData(self::PROP_MAX_WIDTH);

        switch ($maxWidth) {
            case '800':
                return 'max-width-800';
            case '':
                break;
            default:
                $this->logger->warning('[Page Section] unknown max width `' . $maxWidth . '`');
        }
        return null;
    }

    public function getBackground(): string|null
    {
        $background = $this->getData(self::PROP_BACKGROUND);

        switch ($background) {
            case 'medium':
                return 'background-medium';
            case   'desert':
                return 'background-desert';
            case   'swirl':
                return 'background-swirl';
            case '':
                break;
            default:
                $this->logger->warning('[Page Section] unknown background `' . $background . '`');
        }
        return null;
    }

    public function getTopArch(): string|null
    {
        return $this->getData(self::PROP_ARCH_TOP);
    }

    public function getBottomArch(): string|null
    {
        return $this->getData(self::PROP_ARCH_BOTTOM);
    }

    public function getTypes(): string|null
    {
        return $this->getData(self::PROP_TYPES);
    }
}
