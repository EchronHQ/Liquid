<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Framework\DataObject;
use Liquid\Framework\Url;
use Liquid\Framework\View\Element\ArgumentInterface;

class DemoCallToActionBlock extends DataObject implements ArgumentInterface
{
    protected string|null $template = 'element/democalltoaction.phtml';
    private string $title = 'Want to know more?';
    private string $description = 'A modern software architecture to help your business achieve its full potential in just a few clicks.';
    private string $callToActionLabel = 'Book a demo';
    private string $callToActionPage = 'demo';

    public function __construct(
        private readonly Url $url,
        array                $data = []
    )
    {
        parent::__construct($data);
    }

    public function getTitle(): string
    {
        if ($this->hasData('title')) {
            return $this->getData('title');
        }
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        if ($this->hasData('description')) {
            return $this->getData('description');
        }
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
        return $this->url->getPageUrl($this->callToActionPage);
    }
}
