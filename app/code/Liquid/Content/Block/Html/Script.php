<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Html;

class Script
{
    private string $src;
    public string $type = 'text/javascript';

    public bool $async = false;
    public bool $defer = false;

    public string|null $crossorigin = null;
    public string|null $integrity = null;

    public function __construct(string $src)
    {
        $this->src = $src;
    }


    public function getSrc(): string
    {
        return $this->src;
    }


    public function setSrc(string $src): void
    {
        $this->src = $src;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    public function isDefer(): bool
    {
        return $this->defer;
    }


    public function setDefer(bool $defer): void
    {
        $this->defer = $defer;
    }

    public function getCrossorigin(): string|null
    {
        return $this->crossorigin;
    }

    public function getIntegrity(): string|null
    {
        return $this->integrity;
    }


}
