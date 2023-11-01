<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Blog\Repository\TerminologyRepository;
use Attlaz\Connector\Repository\PlatformRepository;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Repository\PageRepository;
use Liquid\Core\Helper\FileHelper;

readonly class GatherPages
{
    public function __construct(
        private BlogRepository        $blogRepository,
        private PageRepository        $pageRepository,
        private TerminologyRepository $terminologyRepository,
        private PlatformRepository    $platformRepository,
        //        private PlatformHelper        $platformHelper,
        private TemplateHelper        $templateHelper,
        private FileHelper            $fileHelper,
    ) {
    }

    /**
     * @return PageDefinition[]
     */
    public function getAllPages(): array
    {
        $result = [];

        $pages = $this->pageRepository->getAll();
        $result = \array_merge($result, $pages);

        /**
         * Blog
         */
        $blogPages = $this->blogRepository->getPages();
        $result = array_merge($result, $blogPages);

        $blogCategories = $this->blogRepository->getCategories();
        $result = \array_merge($result, $blogCategories);

        $blogPosts = $this->blogRepository->getPosts();
        $result = \array_merge($result, $blogPosts);

        $terms = $this->terminologyRepository->getAll();
        $result = \array_merge($result, $terms);

        /**
         * Platform pages
         */
        $platformPages = $this->platformRepository->getPages();
        $result = array_merge($result, $platformPages);

        $connectors = $this->platformRepository->getConnectors();
        $result = \array_merge($result, $connectors);

        $connectorCategories = $this->platformRepository->getCategories();
        $result = \array_merge($result, $connectorCategories);

        $connectorTypes = $this->platformRepository->getTypes();
        $result = \array_merge($result, $connectorTypes);


        //        $addConnectPagesToSitemap = false;
        //        if ($addConnectPagesToSitemap) {
        //
        //            /**
        //             * For now, remove the connect pages from the sitemap
        //             */
        //            foreach ($connectors as $connectorA) {
        //                $connectableConnectors = $this->platformHelper->getConnectablePlatforms($connectorA);
        //                foreach ($connectableConnectors as $connectorB) {
        //                    $result[] = PageDefinition::generate($connectorA->id . ' ' . $connectorB->id, [
        //                        'url_key'  => 'connect/' . $connectorA->urlKey . '-to-' . $connectorB->urlKey,
        //                        'priority' => PageSitemapPriority::LOW
        //                    ]);
        //                }
        //            }
        //        }

        // Sort pages by priority
        usort($result, static function (PageDefinition $a, PageDefinition $b) {
            return strcmp($b->priority->value, $a->priority->value);
        });


        // Append modification date
        foreach ($result as $page) {
            $this->appendModificationDateBasedOnTemplate($page);
        }


        return $result;
    }

    private function appendModificationDateBasedOnTemplate(PageDefinition $page): void
    {
        if ($page->template !== null) {
            try {
                $template = $this->templateHelper->getTemplateFileName($page->template);
                if ($template === null) {
                    return;
                }
                $modificationDate = $this->fileHelper->getFileModificationTime($template);
                if ($modificationDate !== null) {
                    $page->modifiedDate = $modificationDate;
                }
            } catch (\Throwable $ex) {

            }

        }
        // TODO: log if not appending modification date
    }
}
