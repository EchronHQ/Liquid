<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model\Resource;

enum UrlRewriteType: int
{
    case  INTERNAL = 0;
    case  PERMANENT = 301;
    case  TEMPORARY = 302;
}
