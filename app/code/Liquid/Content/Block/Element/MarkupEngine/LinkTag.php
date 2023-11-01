<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;

use Liquid\Content\Block\TemplateBlock;

class LinkTag extends TemplateBlock
{
    protected string|null $template = 'element/markupengine/link.phtml';

    /**
     * @return 'link'|'button'
     */
    public function getType(): string
    {
        $rawType = $this->getData('type');
        if ($rawType !== null) {
            return $rawType;
        }
        return 'link';
    }

    public function getHref(): string|null
    {

        $page = $this->getData('page');
        if ($page !== null) {
            return $this->resolver->getPageUrl($page);
        }
        $link = $this->getData('link');
        if ($link !== null) {
            return $link;
        }

        $this->logger->warning('[Link] No href found');

        return null;
    }

    public function showArrow(): bool
    {
        $arrow = $this->getData('arrow');
        return $arrow === 'true';
    }

    public function getContent(): string
    {
        $content = $this->getData('content');
        if ($content === null || $content === '') {
            $this->logger->warning('[Link] No content defined');
            $content = '[Link]';
        }
        return $content;
    }


}
