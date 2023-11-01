<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Html;

class Stylesheet
{
    private string $rel = 'stylesheet';
    private string $media = 'all';
    private string $href;

    public function __construct(string $href)
    {
        $this->href = $href;
    }

    public function getRel(): string
    {
        return $this->rel;
    }

    public function getMedia(): string
    {
        return $this->media;
    }

    public function getHref(): string
    {
        return $this->href;
    }
}
