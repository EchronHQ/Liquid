<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Element\Template\File;

use Liquid\Framework\Serialize\Serializer\Json;
use Liquid\Framework\View\ViewFileSystem;

class TemplateFileResolver
{
    private array $templateFilesMap = [];

    public function __construct(
        private readonly Json           $serializer,
        private readonly ViewFileSystem $viewFileSystem
    )
    {
    }

    /**
     * Get template filename
     *
     * @param string $template
     * @param array $params
     * @return string|null
     * @throws \JsonException
     */
    public function getTemplateFileName(string $template, array $params = []): string|null
    {
        $key = $template . '_' . $this->serializer->serialize($params);
        if (!isset($this->_templateFilesMap[$key])) {
            $this->templateFilesMap[$key] = $this->viewFileSystem->getTemplateFileName($template, $params);
        }
        return $this->templateFilesMap[$key];
    }
}
