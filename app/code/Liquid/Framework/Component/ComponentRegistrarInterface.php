<?php
declare(strict_types=1);

namespace Liquid\Framework\Component;


interface ComponentRegistrarInterface
{
    /**
     * Get list of registered Magento components
     *
     * Returns an array where key is fully-qualified component name and value is absolute path to component
     */
    public function getPaths(ComponentType $type): array;

    /**
     * Get path of a component if it is already registered
     */
    public function getPath(ComponentType $type, string $componentName): string|null;
}
