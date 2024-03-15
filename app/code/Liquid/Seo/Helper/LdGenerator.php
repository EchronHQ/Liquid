<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;

use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Helper\Resolver;
use Liquid\Seo\Model\LdData;

class LdGenerator
{
    public function __construct(private readonly PageConfig $pageConfig, private readonly Resolver $resolver)
    {
    }


    public function getData(): array
    {


        $domain = 'https://attlaz.com';
        $pageUrl = $this->resolver->getUrl();


        $ld = new LdData($domain);

        $ld->addOrganization('1', 'Attlaz', $this->resolver->getAssetUrl('icon/logo-980x253-white-green.png'));
        $ld->addWebsite('1', 'Attlaz');


        $breadcrumbItems = [];
        $pageBreadcrumbs = $this->pageConfig->getBreadcrumbs();
        foreach ($pageBreadcrumbs as $pageBreadcrumb) {

            $breadcrumbItems[] = [
                "name" => $pageBreadcrumb['name'],
                "url"  => $pageBreadcrumb['url'],
            ];
        }
        $breadcrumbItems[] = [
            "name" => $this->pageConfig->getSeoTitle(),
            "url"  => ''
        ];

        $webPageKey = $pageUrl;
        $ld->addWebPage(
            $webPageKey,
            $pageUrl,
            $this->pageConfig->getSeoTitle(),
            $this->pageConfig->getSeoDescription(),
            $this->resolver->getAssetUrl($this->pageConfig->getImage()),
            $breadcrumbItems
        );

        if ($this->pageConfig->getPageType() === PageConfig::PAGE_TYPE_ARTICLE) {


            $ld->addArticle(
                '557088276570',
                $this->pageConfig->getSeoTitle(),
                $this->pageConfig->getSeoDescription(),
                $webPageKey,
                $this->pageConfig->getPublishDate(),
                $this->pageConfig->getModifiedDate(),
                $this->pageConfig->getAuthor()->name,
                $this->resolver->getAssetUrl($this->pageConfig->getImage())
            );
        }


        return $ld->getData();
    }
}
