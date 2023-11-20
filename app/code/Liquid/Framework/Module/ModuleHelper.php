<?php
declare(strict_types=1);

namespace Liquid\Framework\Module;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Psr\Log\LoggerInterface;

class ModuleHelper
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly LoggerInterface             $logger
    )
    {

    }

    private bool $dataLoaded = false;
    private array $data = [];

    private function parseConfigFile(string $moduleConfigPath): array|null
    {
        $moduleData = null;
        if (file_exists($moduleConfigPath)) {

            $routes = null;
            $viewableEntityRepositories = null;

            include $moduleConfigPath;

            // TODO: get module name
            $moduleIdentifier = $moduleConfigPath;
            $moduleData = [
                'name' => '',
                'order' => 999,
                'routes' => [],
                'viewableEntityRepositories' => [],
            ];

            if (is_array($routes)) {

                $moduleData['routes'] = $routes;
                foreach ($routes as $route => $paths) {
                    if (!is_array($paths)) {
                        $this->logger->error('Route paths should be array', ['module' => $moduleConfigPath, 'paths' => $paths]);
                    }
                }
            }
            if (is_array($viewableEntityRepositories)) {
                $moduleData['viewableEntityRepositories'] = $viewableEntityRepositories;
            }
        }
        return $moduleData;
    }

    public function getModules(): array
    {
        if (!$this->dataLoaded) {

            // TODO: make it possible to order the modules
            $modules = $this->componentRegistrar->getPaths(ComponentType::Module);
            foreach ($modules as $modulePath) {
                $fullPath = $modulePath . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'routes.php';
                $moduleData = $this->parseConfigFile($fullPath);
                if ($moduleData !== null) {
                    $this->data[] = $moduleData;
                }

            }
            $this->dataLoaded = true;
        }
        return $this->data;
    }
}
