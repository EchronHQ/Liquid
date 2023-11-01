<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Core\Helper\FileHelper;
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
        $path = $this->getTemplateFileName($path, $params);
        if ($path === null) {
            return null;
        }
        return $this->fileHelper->getFileContent($path);
    }

    public function getTemplateFileName(string $templatePath, array $params = []): string|null
    {
        /**
         * Content::layout.phtml
         */
        if (\str_contains($templatePath, '::')) {
            $pos = \strpos($templatePath, '::');
            $moduleName = \substr($templatePath, 0, $pos);
            $templatePath = \substr($templatePath, $pos + 2);
        } elseif (isset($params['module'])) {
            $moduleName = $params['module'];
        } else {
            $explode = \explode('\\', \get_class($this));
            $moduleName = $explode[1];
        }

        /**
         * Check themes
         */
        $themes = ['Echron_Default', 'Liquid_Default'];
        foreach ($themes as $themeName) {
            $themePath = $this->componentRegistrar->getPath(ComponentType::Theme, $themeName);
            if ($themePath !== null) {
                $templatePathInTheme = $themePath . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $templatePath;
                if ($this->isValidTemplate($templatePathInTheme)) {
                    return $templatePathInTheme;
                }
            }
        }

        /**
         * Check modules
         */
        $modulePath = $this->componentRegistrar->getPath(ComponentType::Module, $moduleName);

        if ($modulePath !== null) {
            $templatePathInModule = $modulePath . DIRECTORY_SEPARATOR . 'frontend/template/' . $templatePath;
            if ($this->isValidTemplate($templatePathInModule)) {
                return $templatePathInModule;
            }
        }

        /**
         * Test app/template files
         */
//        $templatePathInRoot = ROOT . 'app' . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . $path;
//        if ($this->isValidTemplate($templatePathInRoot)) {
//            return $templatePathInRoot;
//        }
//
//        $enabledModules = [
//            ['path' => ROOT . 'app' . \DIRECTORY_SEPARATOR . 'code' . \DIRECTORY_SEPARATOR . $moduleName,],
//            ['path' => ROOT . 'vendor/echron/liquid/app/code/Liquid/Core'],
//        ];
        /**
         * Test app/code/Module/frontend/template files
         * TODO: only if module is enabled!
         */

//        foreach ($enabledModules as $enabledModule) {
//            $templatePathInModule = $enabledModule['path'] . \DIRECTORY_SEPARATOR . 'frontend' . \DIRECTORY_SEPARATOR . 'template' . \DIRECTORY_SEPARATOR . $path;
//
//            echo $templatePathInModule . '<br/>';
//            if ($this->isValidTemplate($templatePathInModule)) {
//                return $templatePathInModule;
//            }
//        }


//        /**
//         * Test vendor/liquid/
//         */
//        $templatePathInRoot = ROOT . 'vendor' . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . $path;
//        if ($this->isValidTemplate($templatePathInRoot)) {
//            return $templatePathInRoot;
//        }

        $this->logger->error('Unable to find template', [
            'path' => $templatePath,
            'module' => $moduleName,
//            'Path in module' => $templatePathInModule,
//            'Path in root' => $templatePathInRoot,
//            'File exists' => \file_exists($templatePathInModule) ? 'y' : 'n',
//            'Is file' => \is_file($templatePathInModule) ? 'y' : 'n',
        ]);
        throw new \Exception('Invalid template: unable to find template "' . $templatePath . '"  (module ' . $moduleName . ')');
    }

    private function isValidTemplate(string $path): bool
    {
        $inDevMode = true;
        return $this->fileHelper->fileExist($path, !$inDevMode);
    }
}
