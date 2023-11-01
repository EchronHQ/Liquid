<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

enum PageSitemapPriority: string
{
    // 1.0-0.8 Extremely important content, such as your homepage, major category pages, product pages, and subdomain indexes.
    case HIGHEST = '1.0';
    case HIGH = '0.9';
    case MEDIUM = '0.8';

    // 0.7-0.4 Articles, blog posts, category pages, FAQs, system pages. The bulk of your site's content falls into this range.
    case BASE = '0.5';
    case LOW = '0.4';

    // 0.3-0.0 Old news posts, outdated guides, irrelevant pages you nevertheless don't want to delete, merge, or update.
    case LOWEST = '0.3';

    case IGNORE = '0.0';

    public static function fromValue(string $input): self
    {
        switch ($input) {
            case '1.0':
                return self::HIGHEST;
                // no break
            case '0.9':
                return self::HIGH;
            case '0.8':
                return self::MEDIUM;
            case '0.7':
            case '0.6':
            case '0.5':
                return self::BASE;
            case '0.4':
                return self::LOW;
            case '0.3':
            case '0.2':
            case '0.1':
                return self::LOWEST;
            case '0.0':
                return self::IGNORE;
            default:
                throw new \Exception('Unknown PageSitemapPriority `' . $input . '`');
        }
    }
}
