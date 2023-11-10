<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;

use Liquid\Content\Model\Resource\PageSitemapPriority;
use Liquid\Content\Model\SitemapUrlEntry;
use Liquid\Core\Model\Result\Xml;

class GenerateSitemapHelper
{
    /**
     * @param SitemapUrlEntry[] $entries
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function generate(array $entries): \SimpleXMLElement
    {


        $xml = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?>\n" .
            '<urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
  
/>');

        //$url = $xml->addChild('meta');
        //$url->addChild('entries', count($entries) . '');
        foreach ($entries as $entry) {

            if ($entry->priority !== PageSitemapPriority::IGNORE) {


                $url = $xml->addChild('url');
                if ($url === null) {
                    throw new \Exception('Unable to create URL node');
                }
                $url->addChild('loc', $entry->loc);

                foreach ($entry->getAlternatives() as $alternativeHreflang => $alternativeHref) {
                    $link = $url->addChild('xhtml:xhtml:link');
                    if ($link !== null) {
                        $link->addAttribute('rel', 'alternate');
                        $link->addAttribute('hreflang', $alternativeHreflang);
                        $link->addAttribute('href', $alternativeHref);
                    }


                }
                $url->addChild('priority', $entry->priority->value);

                if ($entry->lastmod !== null) {
                    // Optional http://www.w3.org/TR/NOTE-datetime
                    $url->addChild('lastmod', $entry->lastmod->format('c'));
                }
                if ($entry->changeFrequency !== null) {
                    $url->addChild('changefreq', $entry->changeFrequency->value);
                }
            }

        }

        // TODO add validation (apparently simplexml doesn't support validation)

        return $xml;
    }

    public static function getModificationDate(string $filePath): \DateTime|null
    {
        if (!\file_exists($filePath)) {
            return null;
        }
        $time = \filemtime($filePath);
        if ($time === false) {
            return null;
        }
        return \DateTime::createFromFormat('U', (string)$time);
    }

    public static function store(\SimpleXMLElement $sitemapXml, string $filePath): void
    {
        $result = new Xml();

        $sitemapXml->asXML($filePath);
        $result->setData($sitemapXml->asXML());
    }
}
