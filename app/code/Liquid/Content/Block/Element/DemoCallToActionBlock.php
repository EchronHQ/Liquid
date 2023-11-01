<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Content\Block\TemplateBlock;

class DemoCallToActionBlock extends TemplateBlock
{
    private string $title = 'Want to know more?';
    private string $description = 'A modern software architecture to help your business achieve its full potential in just a few clicks.';

    protected string|null $template = 'element/democalltoaction.phtml';
    private string $callToActionLabel = 'Book a demo';
    private string $callToActionPage = 'demo';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    public function setCallToAction(string $label, string $page): void
    {
        $this->callToActionLabel = $label;
        $this->callToActionPage = $page;
    }

    public function getCallToActionLabel(): string
    {
        return $this->callToActionLabel;
    }

    public function getCallToActionPage(): string
    {
        return $this->getResolver()->getPageUrl($this->callToActionPage);
    }
}
