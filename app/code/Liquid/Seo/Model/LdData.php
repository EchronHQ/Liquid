<?php

declare(strict_types=1);

namespace Liquid\Seo\Model;

use Liquid\Core\Model\FrontendFileUrl;

class LdData
{
    private array $sections = [];

    private array $ids = [];

    public function __construct(private readonly string $domain)
    {

    }

    public function getData(): array
    {
        return $this->sections;
    }

    public function addImage(FrontendFileUrl $image, string $key): void
    {
        $id = $this->domain . "/#/schema/ImageObject/" . $this->formatKey($key);
        $this->ids['image:' . $key] = $id;
        $this->sections[] = [
            "@type"      => "ImageObject",
            "@id"        => $id,
            "url"        => $image->url,
            "contentUrl" => $image->url,
            "width"      => $image->width,
            "height"     => $image->height

            //            "caption" => '',
            //        'inlanguage' => 'en'
        ];
    }

    public function getImageId(string $key): string
    {
        return $this->ids['image:' . $key];
    }

    public function addOrganization(string $key, string $name, FrontendFileUrl $image): void
    {
        $this->addImage($image, '23931056685146');

        $id = $this->domain . "/#/schema/organization/" . $this->formatKey($key);
        $this->ids['organization:' . $key] = $id;

        $this->sections[] = [
            "@type"  => "Organization",
            "@id"    => $id,
            "url"    => $this->domain,
            "name"   => $name,
            "logo"   => [
                "@id" => $this->getImageId('23931056685146')
            ],
            "image"  => [
                [
                    "@id" => $this->getImageId('23931056685146')
                ]
            ],
            "sameAs" => [
                "https://www.facebook.com/attlaz",
                "https://twitter.com/AttlazHQ",
                "https://www.linkedin.com/company/attlaz",
                "https://www.instagram.com/attlazhq/"
            ]
        ];
    }

    public function getOrganizationId(string $key): string
    {
        return $this->ids['organization:' . $key];
    }

    public function addWebsite(string $key, string $name): void
    {
        $id = $this->domain . "/#/schema/website/" . $this->formatKey($key);
        $this->ids['website:' . $key] = $id;

        $this->sections[] = [
            "@type"      => "WebSite",
            "@id"        => $id,
            "url"        => $this->domain,
            "name"       => $name,
            //            "potentialAction" => [
            //                "@type"       => "SearchAction",
            //                "target"      => $domain . "/search?q={search_term_string}",
            //                "query-input" => "required name=search_term_string"
            //            ],
            "publisher"  => [
                "@id" => $this->getOrganizationId('1')
            ],
            "inLanguage" => "en"
        ];
    }

    public function getWebsiteId(string $key): string
    {
        return $this->ids['website:' . $key];
    }

    public function addWebPage(string $key, string $url, string $name, string $description, FrontendFileUrl $image, array $breadcrumbItems = []): void
    {
        $this->addImage($image, '23931057111130');

        $id = $this->formatKey($this->domain);
        $this->ids['webpage:' . $key] = $id;

        if (count($breadcrumbItems) > 0) {
            $this->addBreadcrumbList('', $breadcrumbItems);

            $webPageData['breadcrumb'] = [
                "@id" => $this->getBreadcrumbListId(''),
            ];
        }

        // TODO: when is a page a collectionpage?
        $webPageData = [
            "@type"              => ["WebPage", "CollectionPage"],
            "@id"                => $id,
            "url"                => $url,
            "name"               => $name,
            "description"        => $description,
            "about"              => [
                "@id" => $this->getOrganizationId('1')
            ],
            "primaryImageOfPage" => [
                "@id" => $this->getImageId('23931057111130')
            ],
            "image"              => [
                [
                    "@id" => $this->getImageId('23931057111130')
                ]
            ],
            "isPartOf"           => [
                "@id" => $this->getWebsiteId('1')
            ]
        ];

        $this->sections[] = $webPageData;
    }

    public function getWebPageId(string $key): string
    {
        return $this->ids['webpage:' . $key];
    }

    public function addBreadcrumbList(string $key, array $breadcrumbItems): void
    {
        $breadcrumbListId = $this->domain . "/#/schema/breadcrumb";
        $this->ids['breadcrumb-list:' . $key] = $breadcrumbListId;

        $breadcrumbList = [];

        foreach ($breadcrumbItems as $breadcrumbItem) {

            $breadcrumbList[] = [
                "@type"    => "ListItem",
                "name"     => $breadcrumbItem['name'],
                "item"     => $breadcrumbItem['url'],
                "position" => count($breadcrumbList) + 1
            ];
        }

        $this->sections[] = [
            "@type"           => "BreadcrumbList",
            "@id"             => $breadcrumbListId,
            "itemListElement" => $breadcrumbList
        ];


    }

    public function getBreadcrumbListId(string $key): string
    {
        return $this->ids['breadcrumb-list:' . $key];
    }

    public function addArticle(string $key, string $headline, string $description, string $webPageKey, \DateTime $published, \DateTime $modified, string $author, FrontendFileUrl $image): void
    {

        $id = $this->domain . "/#/schema/article/" . $this->formatKey($key);
        $this->ids['article:' . $key] = $id;

        $this->addImage($image, '54545454');
        $this->sections[] = [
            "@type"            => "Article",
            "@id"              => $id,
            "headline"         => $headline,
            "description"      => $description,
            "isPartOf"         => [
                "@id" => $this->getWebPageId($webPageKey)
            ],
            "mainEntityOfPage" => [
                "@id" => $this->getWebPageId($webPageKey)
            ],
            "dataPublished"    => $published->format(\DateTimeInterface::ATOM),
            "dateModified"     => $modified->format(\DateTimeInterface::ATOM),
            "publisher"        => [
                "@id" => $this->getOrganizationId('1')
            ],
            "author"           => [
                '@type' => 'Person',
                '@id'   => $this->domain . '/#/schema/person/' . $this->formatKey($author),
                "name"  => $author
            ],
            "articleSection"   => ['Press Release'],
            "image"            => [
                "@id" => $this->getImageId('54545454')
            ]
        ];
    }

    private function formatKey(string $input): string
    {
        return \strtolower(\preg_replace('/[^a-z0-9 ]/i', '-', $input));
    }
}
