<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Psr\Log\LoggerInterface;

readonly class TemplateHelper
{
    public function __construct(
        private FileHelper                  $fileHelper,
        private ComponentRegistrarInterface $componentRegistrar,
        private LoggerInterface             $logger
    )
    {

    }

    public function getTemplateFileContent(string $path, array $params = []): string|null
    {
        $templatePath = $this->getTemplateFileName($path, $params);
        if ($templatePath === null) {
            return null;
        }
        return $this->fileHelper->getFileContent($templatePath);
    }

    public function getTemplateFileName(string $templatePath, array $params = []): string|null
    {


        [$moduleName, $filePath] = Resolver::extractModule($templatePath);

        if (empty($moduleName) && isset($params['module'])) {
            $moduleName = $params['module'];
        }

        if (empty($moduleName)) {
            $explode = \explode('\\', \get_class($this));
            $moduleName = $explode[0] . '_' . $explode[1];
        }


        /**
         * Check themes
         */
        $themePaths = $this->componentRegistrar->getPaths(ComponentType::Theme);

        foreach ($themePaths as $themePath) {
//            $themePath = $this->componentRegistrar->getPath(ComponentType::Theme, $themeName);
//            if ($themePath !== null) {
            $templatePathInTheme = $themePath . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $filePath;
            if ($this->isValidTemplate($templatePathInTheme)) {
                return $templatePathInTheme;
            }
//            }
        }

//        var_dump($moduleName);
        if ($moduleName !== null) {
            /**
             * Check modules
             */

            $modulePath = $this->componentRegistrar->getPath(ComponentType::Module, $moduleName);

            if ($modulePath !== null) {
                $templatePathInModule = $modulePath . DIRECTORY_SEPARATOR . 'frontend/template/' . $filePath;
                if ($this->isValidTemplate($templatePathInModule)) {
                    return $templatePathInModule;
                }
            }
        }


        $this->logger->error('Unable to find template', [
            'path' => $templatePath,
            'file' => $filePath,
            'module' => $moduleName,
        ]);
        throw new \Exception('Invalid template: unable to find template "' . $templatePath . '"  (module ' . $moduleName . ')');
    }

    private function isValidTemplate(string $path): bool
    {
        $inDevMode = true;
        return $this->fileHelper->fileExist($path, !$inDevMode);
    }
}
