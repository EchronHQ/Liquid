<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;

use DI\Container;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\Resource\PageStatus;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Repository\ViewableEntityRepository;
use Liquid\Framework\Module\ModuleHelper;
use Psr\Log\LoggerInterface;

readonly class GatherPages
{
    public function __construct(
        private ModuleHelper    $moduleHelper,
        private TemplateHelper  $templateHelper,
        private FileHelper      $fileHelper,
        private Container       $diContainer,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @return PageDefinition[]
     */
    public function getAllPages(): array
    {
        $result = [];

        $modules = $this->moduleHelper->getModules();


        foreach ($modules as $module) {
            echo '# ' . $module->name . PHP_EOL;
            $moduleViewableEntityRepositories = $module->viewableEntityRepositories;
            foreach ($moduleViewableEntityRepositories as $viewableEntityRepository) {
                $respository = $this->diContainer->get($viewableEntityRepository);
                if ($respository instanceof ViewableEntityRepository) {
                    $pages = $respository->getEntities();
                    foreach ($pages as $page) {
                        if ($page->status === PageStatus::ACTIVE) {
                            $result[] = $page;

                            echo ' - ' . $page->getSeoTitle() . ' ' . $page->getUrlPath() . PHP_EOL;
                        }

                    }
                } else {
                    $this->logger->error('Viewable repository must be instance of ViewableEntityRepository');
                }
            }
        }


//        $pages = $this->pageRepository->getAll();
//        $result = \array_merge($result, $pages);
//
//        /**
//         * Blog
//         */
//        $blogPages = $this->blogRepository->getPages();
//        $result = array_merge($result, $blogPages);
//
//        $blogCategories = $this->blogRepository->getCategories();
//        $result = \array_merge($result, $blogCategories);
//
//        $blogPosts = $this->blogRepository->getPosts();
//        $result = \array_merge($result, $blogPosts);
//
//        $terms = $this->terminologyRepository->getAll();
//        $result = \array_merge($result, $terms);
//
//        /**
//         * Platform pages
//         */
//        $platformPages = $this->platformRepository->getPages();
//        $result = array_merge($result, $platformPages);
//
//        $connectors = $this->platformRepository->getConnectors();
//        $result = \array_merge($result, $connectors);
//
//        $connectorCategories = $this->platformRepository->getCategories();
//        $result = \array_merge($result, $connectorCategories);
//
//        $connectorTypes = $this->platformRepository->getTypes();
//        $result = \array_merge($result, $connectorTypes);


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
