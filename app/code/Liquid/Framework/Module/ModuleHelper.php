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
    /**
     * @var ModuleData[]
     */
    private array $data = [];

    private function parseConfigFile(string $moduleConfigPath): ModuleData|null
    {
        $moduleData = null;
        if (file_exists($moduleConfigPath)) {

            $name = null;
            $routes = null;
            $viewableEntityRepositories = null;

            include $moduleConfigPath;

            $moduleIdentifier = $name ?? $moduleConfigPath;

            $moduleData = new ModuleData($moduleIdentifier);


            if (is_array($routes)) {

                $moduleData->routes = $routes;
                foreach ($routes as $route => $paths) {
                    if (!is_array($paths)) {
                        $this->logger->error('Route paths should be array', ['module' => $moduleIdentifier, 'paths' => $paths]);
                    }
                }
            }
            if (is_array($viewableEntityRepositories)) {
                $moduleData->viewableEntityRepositories = $viewableEntityRepositories;
            }
        }
        return $moduleData;
    }

    /**
     * @return ModuleData[]
     */
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
