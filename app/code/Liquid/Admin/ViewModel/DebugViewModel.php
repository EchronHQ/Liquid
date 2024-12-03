<?php
declare(strict_types=1);

namespace Liquid\Admin\ViewModel;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Model\Resource\PageStatus;
use Liquid\Core\Helper\FileHelper;
use Liquid\Framework\App\Entity\AggregateEntityResolver;
use Liquid\Framework\Module\ModuleHelper;
use Liquid\Framework\View\Element\ArgumentInterface;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Psr\Log\LoggerInterface;

class DebugViewModel implements ArgumentInterface
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
     * @return AbstractViewableEntity[]
     */
    public function x(): array
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

        return $result;
    }
}
