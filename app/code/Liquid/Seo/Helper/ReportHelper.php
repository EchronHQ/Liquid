<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;

use Liquid\Content\Model\Resource\PageSitemapChangeFrequency;
use Liquid\Content\Model\Resource\PageSitemapPriority;
use Liquid\Content\Model\SitemapUrlEntry;
use Liquid\Core\Helper\Resolver;
use Liquid\Seo\Model\DownloadedPage;
use Echron\Tools\XmlHelper;
use Psr\Log\LoggerInterface;

class ReportHelper
{
    public function __construct(
        private readonly Resolver        $resolver,
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * @return SitemapUrlEntry[]
     * @throws \Exception
     */
    public function getAllPages(string $sitemapUrl): array
    {

        $sitemapXml = \file_get_contents($sitemapUrl);
        $xml = XmlHelper::parseStringToSimpleXml($sitemapXml);

        $pages = [];
        /** @var \SimpleXMLElement $url */
        foreach ($xml as $url) {
            $changeFrequency = PageSitemapChangeFrequency::NEVER;
            try {
                $changeFrequency = PageSitemapChangeFrequency::fromValue((string)$url->changefreq);
            } catch (\Throwable $ex) {
                // $this->logger->info('Unable to parse change frequency `' . $url->changefreq . '` for url `' . $url->loc . '`');
            }

            $page = new SitemapUrlEntry((string)$url->loc, PageSitemapPriority::fromValue((string)$url->priority), $changeFrequency);


            if ((string)$url->lastmod !== '') {
                $lastmod = \DateTime::createFromFormat('c', (string)$url->lastmod);
                if ($lastmod !== false) {
                    $page->lastmod = $lastmod;
                }
                //else {
                // TODO: check this out
                //$this->logger->error('Unable to parse lastmod date', ['lastmod' => (string)$url->lastmod]);
                //}
            }
            $pages[] = $page;
        }

        return $pages;

    }

    /**
     * @param SitemapUrlEntry[] $pages
     * @return array
     */
    public function generate(array $pages): array
    {
        // Sort pages by priority
        \usort($pages, static function (SitemapUrlEntry $a, SitemapUrlEntry $b) {
            return $b->priority <=> $a->priority;
        });

        $issues = [];

        /**
         * Get duplicate descriptions and titles
         */

        $titles = [];
        $descriptions = [];

        $downloadedPages = [];
        foreach ($pages as $page) {
            $downloadedPage = $this->getPageRawContent($page);
            if ($downloadedPage !== null) {
                $downloadedPages[] = $downloadedPage;
            }
        }


        /**
         * Collect used meta titles and meta descriptions
         */
        foreach ($downloadedPages as $downloadedPage) {
            if ($downloadedPage !== null) {
                if (!isset($titles[$downloadedPage->getSeoTitle()])) {
                    $titles[$downloadedPage->getSeoTitle()] = [];
                }
                $titles[$downloadedPage->getSeoTitle()][] = $downloadedPage;

                if (!isset($descriptions[$downloadedPage->getMetaDescription()])) {
                    $descriptions[$downloadedPage->getMetaDescription()] = [];
                }
                $descriptions[$downloadedPage->getMetaDescription()][] = $downloadedPage;
            }
        }

        /**
         * Combine issues
         */
        foreach ($downloadedPages as $downloadedPage) {
            $pageIssues = $this->validatePage($downloadedPage);


            if (count($titles[$downloadedPage->getSeoTitle()]) > 1) {
                $pageIssues[] = 'Duplicate title "' . $downloadedPage->getSeoTitle() . '"';
            }
            if (count($descriptions[$downloadedPage->getMetaDescription()]) > 1) {
                $pageIssues[] = 'Duplicate description "' . $downloadedPage->getMetaDescription() . '"';
            }

            if (count($pageIssues) > 0) {
                $issues[$downloadedPage->getUrl()] = [
                    'page' => $downloadedPage,
                    'issues' => $pageIssues,
                ];
            }

        }
        // TODO: order the issues by the priority of the page

        return $issues;
    }

    private function validatePage(DownloadedPage $page): array
    {
        $issues = [];
        $title = $page->getSeoTitle();
        // Title should be between x and 70 characters
        $titleLength = \strlen($title);
        if ($titleLength < 25) {
            $issues[] = 'Title is too short: "' . $title . '" (' . $titleLength . ' characters, minimum 25 characters)';
        }
        if ($titleLength > 70) {
            $issues[] = 'Title is too long: "' . $title . '" (' . $titleLength . ' characters, maximum 70 characters)';
        }

        //Meta description should be between 25 and 160 characters
        $description = $page->getMetaDescription();
        $descriptionLength = \strlen($description);
        if ($descriptionLength < 25) {
            $issues[] = 'Description is too short: "' . $description . '" (' . $descriptionLength . ' characters, minimum 25 characters)';
        }
        if ($descriptionLength > 160) {
            $issues[] = 'Description is too long: "' . $description . '" (' . $descriptionLength . ' characters, maximum 160 characters)';
        }

        // Check text to html ratio (should be above 10%)
        $htmlToTextRatio = $page->getTextToHtmlRatio();

        if ($htmlToTextRatio <= 0.1) {
            $issues[] = 'low text-HTML ratio: "' . \round($htmlToTextRatio, 3) . '" (should be more than 0.1)';
        }

        // Check word count
        $wordCount = $page->getWordCount();

        if ($wordCount < 200) {
            $issues[] = 'low word count: "' . $wordCount . '" (should be more 200)';
        }
        /**
         * Validate for untranslated terms
         */


        return $issues;
    }


    public function getPageRawContent(SitemapUrlEntry $page): DownloadedPage|null
    {
        try {
            if (\str_contains($page->loc, 'localhost')) {
                // TODO: throw error
                return null;
            }
            $htmlContent = @\file_get_contents($page->loc);
            if ($htmlContent === false) {
                throw new \Error('Unable to get content of ' . $this->resolver->getUrl($page->loc));
            }
            return new DownloadedPage($page->loc, $htmlContent);
        } catch (\Throwable $ex) {
            $this->logger->error('Unable to get page content', ['url' => $page->loc, 'ex' => $ex->getMessage()]);
        }
        return null;
    }


}
