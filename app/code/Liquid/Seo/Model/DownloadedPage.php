<?php

declare(strict_types=1);

namespace Liquid\Seo\Model;

use Liquid\Content\Helper\HtmlHelper;

class DownloadedPage
{
    private bool $parsed = false;

    private string $metaTitle = '';
    private string $metaDescription = '';
    private string $metaKeywords = '';

    private string $strippedHtmlContent;

    public function __construct(private readonly string $url, private readonly string $htmlContent)
    {

    }

    private function parse(): void
    {
        $this->strippedHtmlContent = HtmlHelper::removeHtml($this->htmlContent);


        try {
            $doc = new \DOMDocument();
            @$doc->loadHTML($this->htmlContent);
            $nodes = $doc->getElementsByTagName('title');


            $this->metaTitle = $nodes->item(0)->nodeValue;

            $metas = $doc->getElementsByTagName('meta');

            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);
                if ($meta !== null) {
                    if ($meta->getAttribute('name') === 'description') {
                        $this->metaDescription = $meta->getAttribute('content');
                    }
                    if ($meta->getAttribute('name') === 'keywords') {
                        $this->metaKeywords = $meta->getAttribute('content');
                    }
                }

            }
        } catch (\Throwable $ex) {

        }

    }


    public function getTextToHtmlRatio(): float
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return \strlen($this->strippedHtmlContent) / \strlen($this->htmlContent);
    }

    public function getHtmlCharacterCount(): int
    {
        return \strlen($this->htmlContent);
    }

    public function getTextCharacterCount(): int
    {
        return \strlen($this->strippedHtmlContent);
    }

    public function getWordCount(): int
    {
        if (!$this->parsed) {
            $this->parse();
        }
        //   try {


        // TODO: should we filter out meaningless words like "is", "and","or", ...
        //            if ($page->urlKey === 'solutions') {
        //                \var_dump($contentWithoutHtml);
        //                die('---');
        //                \var_dump((str_word_count(strip_tags($htmlContent), 1)));
        //                die('--');
        //            }
        return count(\array_unique(\str_word_count($this->strippedHtmlContent, 1)));
        // } catch (\Throwable $ex) {
        //            $this->logger->error('Unable to determine word count', ['ex' => $ex]);
        //}

    }


    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSeoTitle(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->metaTitle;
    }

    public function getMetaDescription(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->metaDescription;
    }

    public function getMetaKeywords(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }
        return $this->metaKeywords;
    }
}
