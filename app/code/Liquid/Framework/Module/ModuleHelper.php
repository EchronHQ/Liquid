<?php
declare(strict_types=1);

namespace Liquid\Framework\Module;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Exception\ContextException;

class ModuleHelper
{
    private bool $dataLoaded = false;
    /**
     * @var ModuleData[]
     */
    private array $data = [];

    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        // private readonly LoggerInterface             $logger
    )
    {

    }

    public function getCodes(): array
    {
        $result = [];
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $result[] = $module->code;
        }
        return $result;
    }

    /**
     * @return ModuleData[]
     */
    public function getModules(): array
    {
        if (!$this->dataLoaded) {

            // TODO: make it possible to order the modules
            $modules = $this->componentRegistrar->getPaths(ComponentType::Module);
            foreach ($modules as $moduleCode => $modulePath) {
                $moduleData = $this->parseConfigFile($moduleCode, $modulePath);
                if ($moduleData !== null) {
                    $this->data[] = $moduleData;
                }

            }
            $this->dataLoaded = true;
        }
        return $this->data;
    }

    private function parseConfigFile(string $moduleCode, string $modulePath): ModuleData|null
    {
        $moduleData = null;
        $moduleConfigPath = $modulePath . '/etc/config.php';
        if (\file_exists($moduleConfigPath)) {

            $config = $this->readFile($moduleConfigPath);

            $name = null;
            $routes = null;
            $viewableEntityRepositories = null;


            $moduleData = new ModuleData($moduleCode, $modulePath);
            $moduleData->name = $config['name'];
            $moduleData->setSortOrder($config['sortOrder']);
            // Set enable when value not set in config or value is set in config to true
            $moduleData->enabled = !isset($config['enabled']) || $config['enabled'] === true;

            if (\is_array($routes)) {

                $moduleData->routes = $routes;
                foreach ($routes as $route => $paths) {
                    if (!\is_array($paths)) {
                        throw new ContextException('Route paths should be array', ['module' => $moduleData->name, 'paths' => $paths]);
                    }
                }
            }
            if (\is_array($viewableEntityRepositories)) {
                $moduleData->viewableEntityRepositories = $viewableEntityRepositories;
            }
            unset($config);


        }
        return $moduleData;
    }

    /**
     * TODO: extract method in separate class (also used in config reader)
     *
     * @param string $file
     * @return array
     * @throws \Exception
     */
    private function readFile(string $file): array
    {

        $definitions = require $file;

        if (!\is_array($definitions)) {
            throw new \Exception("File $file should return an array of definitions");
        }
        return $definitions;
    }
}
