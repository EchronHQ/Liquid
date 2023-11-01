<?php

declare(strict_types=1);

namespace Liquid\Content\Model;

class Locale
{
    public string $code;
    public string $langCode;

    // https://developer.yoast.com/features/opengraph/api/changing-og-locale-output/
    //    public string $openGraphCode;
    public bool $active = true;
}
