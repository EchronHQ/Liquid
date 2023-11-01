<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

class FrontendFileUrl
{
    public function __construct(public readonly string $url, public readonly int|null $width = null, public int|null $height = null)
    {

    }

    public function __toString(): string
    {
        // TODO: should not be used, log this
        return $this->url;
    }
}
