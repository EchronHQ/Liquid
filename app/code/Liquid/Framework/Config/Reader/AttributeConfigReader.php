<?php
declare(strict_types=1);

namespace Liquid\Framework\Config\Reader;

use FilesystemIterator;
use Liquid\Framework\App\Route\Attribute\Route as ActionClassAttribute;
use Liquid\Framework\Module\File\Dir;
use Liquid\Framework\Module\ModuleHelper;
use Psr\Log\LoggerInterface;

/**
 * @template T
 */
class AttributeConfigReader implements ConfigReaderInterface
{
    /**
     * @param ModuleHelper $modulesList
     * @param Dir $moduleDir
     * @param LoggerInterface $logger
     * @param class-string<T> $attributeClassName
     * @param string $moduleDirType
     */
    public function __construct(
        private readonly ModuleHelper    $modulesList,
        private readonly Dir             $moduleDir,
        private readonly LoggerInterface $logger,
        private readonly string          $attributeClassName,
        private readonly string          $moduleDirType
    )
    {

    }

    /**
     * @return array{string,T}
     * @throws \ReflectionException
     */
    public function read(string|null $scope = null): array
    {
        if (!class_exists($this->attributeClassName, true)) {
            throw new \RuntimeException('Unable to read configuration, class "' . $this->attributeClassName . '" does not exist');
        }
        // TODO: filter actions by scope
        $classNames = $this->getClassNames();
        $routes = [];
        foreach ($classNames as $className) {
            if (!class_exists($className, true)) {
                $this->logger->error('[Attribute config reader] Invalid class "' . $className . '" (class does not seem to exist)');
            } else {


                $reflectionClass = new \ReflectionClass($className);

                $attributes = $reflectionClass->getAttributes($this->attributeClassName);
                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === $this->attributeClassName) {
                        /** @var ActionClassAttribute $actionClassData */
                        $actionClassData = $attribute->newInstance();
                        $routes[$className] = $actionClassData;
                    }
                }
            }

        }
        return $routes;
    }


    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    protected function getModuleDir(string $type, string $moduleName): string
    {
        return $this->moduleDir->getDir($moduleName, $type);
    }

    private function getClassNames(): array
    {
        $actions = [];
        foreach ($this->modulesList->getCodes() as $moduleName) {
            $actionDir = $this->getModuleDir($this->moduleDirType, $moduleName);
            if (!file_exists($actionDir)) {
                continue;
            }
            $dirIterator = new \RecursiveDirectoryIterator($actionDir, FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
            $namespace = str_replace('_', '\\', $moduleName);
            /** @var \SplFileInfo $file */
            foreach ($recursiveIterator as $file) {
                $actionName = str_replace('/', '\\', str_replace($actionDir, '', $file->getPathname()));
                $className = $namespace . "\\" . $this->moduleDirType . substr($actionName, 0, -4);
                $actions[strtolower($className)] = $className;
            }
        }
        return $actions;
    }
}
