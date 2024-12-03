<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Blog\Model\PostDefinition;
use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Model\View\Page\PageConfig;

class PageConfigHelper
{
    public static function append(AbstractViewableEntity $pageDefinition, PageConfig $pageConfig): void
    {
        $pageConfig->setPageDefinition($pageDefinition);


        //        $pageConfig->setSeoTitle($pageDefinition->getSeoTitle());
        $pageConfig->setSeoDescription($pageDefinition->metaDescription);
        $pageConfig->setSeoKeywords($pageDefinition->metaKeywords);

        // TODO: add breadcrumbs
        //$pageConfig->addBreadcrumb($name,$url);

        if ($pageDefinition instanceof PostDefinition) {
            $pageConfig->setPublisher($pageDefinition->publisher);
            $pageConfig->setAuthor($pageDefinition->author);
            $pageConfig->setPublishDate($pageDefinition->publishDate);
            $pageConfig->setModifiedDate($pageDefinition->modifiedDate);
            $pageConfig->setPageType(PageConfig::PAGE_TYPE_ARTICLE);
            $pageConfig->setImage($pageDefinition->image);
        }
        // TODO: append other data such as images, ...

        $bodyClasses = \explode(' ', $pageDefinition->docCssClass);
        foreach ($bodyClasses as $class) {
            $pageConfig->addBodyClass($class);
        }

    }
}
