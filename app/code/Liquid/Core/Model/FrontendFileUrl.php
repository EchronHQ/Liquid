<?php
declare(strict_types=1);

namespace Liquid\Core\Model;

class FrontendFileUrl
{
    public string|null $path = null;

    public function __construct(
        public readonly string   $url,
        public readonly int|null $width = null,
        public int|null          $height = null
    )
    {

    }

    /**
     * @return string
     * @deprecated
     */
    public function __toString(): string
    {
        // TODO: should not be used, log this
        return $this->url;
    }
}
