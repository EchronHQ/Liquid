<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Core\Helper\FileHelper;
use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Psr\Log\LoggerInterface;

readonly class TemplateHelper
{
    public function __construct(
        private FileHelper                    $fileHelper,
        private readonly TemplateFileResolver $templateFileResolver,
        private ComponentRegistrarInterface   $componentRegistrar,
        private LoggerInterface               $logger
    )
    {

    }

    public function getTemplateFileContent(string $path, array $params = []): string|null
    {
        $templatePath = $this->templateFileResolver->getTemplateFileName($path, $params);
        if ($templatePath === null) {
            return null;
        }
        return $this->fileHelper->getFileContent($templatePath);
    }
}
