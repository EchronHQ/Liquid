<?php

declare(strict_types=1);

namespace Liquid\Seo\Helper;


use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\Resource\PageStatus;
use Liquid\Core\Helper\FileHelper;
use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Entity\AggregateEntityResolver;
use Liquid\Framework\Module\ModuleHelper;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Psr\Log\LoggerInterface;

readonly class GatherPages
{
    public function __construct(
        private ModuleHelper            $moduleHelper,
        private TemplateFileResolver    $templateFileResolver,
        private FileHelper              $fileHelper,
        private AggregateEntityResolver $entityResolver,
        private LoggerInterface         $logger
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


        $entities = $this->entityResolver->getEntities();
        foreach ($entities as $entity) {
            if ($entity->status === PageStatus::ACTIVE) {
                $result[] = $entity;

                // echo ' - ' . $entity->getUrlPath() . ' (' . $entity->getSeoTitle() . ') ' . PHP_EOL;
            }
        }

        // Sort pages by priority
        \usort($result, static function (AbstractViewableEntity $a, AbstractViewableEntity $b) {
            return \strcmp($b->priority->value, $a->priority->value);
        });


        // Append modification date
        foreach ($result as $page) {
            $this->appendModificationDateBasedOnTemplate($page);
        }


        return $result;
    }

    private function appendModificationDateBasedOnTemplate(AbstractViewableEntity $page): void
    {
        if ($page->template !== null) {
            try {
                $template = $this->templateFileResolver->getTemplateFileName($page->template, ['area' => AreaCode::Frontend]);
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
