<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

enum UrlType
{
    case LINK;
    case MEDIA;
    case STATIC;
    case WEB;
    case JS;
}
