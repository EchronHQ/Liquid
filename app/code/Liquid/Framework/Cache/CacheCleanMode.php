<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache;

enum CacheCleanMode
{
    case All;
    case Old;

    case MatchingTag;
    case NotMatchingTag;
    case MatchingAnyTag;
}
