<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

class UriParser extends \Spatie\Url\Url
{
    public function parse(string $input): \Spatie\Url\Url
    {
        return \Spatie\Url\Url::fromString($input);
    }
}
