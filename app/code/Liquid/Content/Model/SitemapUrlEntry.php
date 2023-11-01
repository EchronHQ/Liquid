<?php

declare(strict_types=1);

namespace Liquid\Content\Model;

use Liquid\Content\Model\Resource\PageSitemapChangeFrequency;
use Liquid\Content\Model\Resource\PageSitemapPriority;

class SitemapUrlEntry
{
    public \DateTime|null $lastmod = null;
    private array $alternatives = [];

    public function __construct(public string $loc, public PageSitemapPriority $priority, public PageSitemapChangeFrequency|null $changeFrequency = null)
    {
    }

    public function addAlternative(string $hreflang, string $loc): void
    {
        $this->alternatives[$hreflang] = $loc;
    }

    public function getAlternatives(): array
    {
        return $this->alternatives;
    }
}
