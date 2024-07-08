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
        return match (\strtolower($input)) {
            'always' => self::ALWAYS,
            'hourly' => self::HOURLY,
            'daily' => self::DAILY,
            'weekly' => self::WEEKLY,
            'montly' => self::MONTHLY,
            'yearly' => self::YEARLY,
            'never' => self::NEVER,
            default => throw new \Exception('Unknown page sitemap change frequency "' . $input . '"'),
        };
    }
}
