<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

enum PageSitemapChangeFrequency: string
{
    /**
     * The value "always" should be used to describe documents that change each time they are accessed.
     * The value "never" should be used to describe archived URLs.
     *
     * Please note that the value of this tag is considered a hint and not a command.
     * Even though search engine crawlers may consider this information when making decisions,
     * they may crawl pages marked "hourly" less frequently than that, and they may crawl pages marked "yearly" more frequently than that.
     * Crawlers may periodically crawl pages marked "never" so that they can handle unexpected changes to those pages.
     */

    case ALWAYS = 'always';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case NEVER = 'never';

    public static function fromValue(string $input): self
    {
        switch (\strtolower($input)) {
            case 'always':
                return self::ALWAYS;
                // no break
            case 'hourly':
                return self::HOURLY;
            case 'daily':
                return self::DAILY;
            case 'weekly':
                return self::WEEKLY;
            case 'montly':
                return self::MONTHLY;
            case 'yearly':
                return self::YEARLY;
            case 'never':
                return self::NEVER;

            default:
                throw new \Exception('Unknown page sitemap change frequency "' . $input . '"');
        }
    }
}
