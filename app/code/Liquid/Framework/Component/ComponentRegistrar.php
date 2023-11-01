<?php
declare(strict_types=1);

namespace Liquid\Framework\Component;

class ComponentRegistrar implements ComponentRegistrarInterface
{


    private static array $paths = [
        ComponentType::Module->name => [],
        ComponentType::Theme->name => [],
    ];

    /**
     * Sets the location of a component.
     *
     * @param ComponentType $type component type
     * @param string $componentName Fully-qualified component name
     * @param string $path Absolute file path to the component
     * @return void
     * @throws \LogicException
     */
    public static function register(ComponentType $type, string $componentName, string $path): void
    {
        if (isset(self::$paths[$type->name][$componentName])) {
            throw new \LogicException(
                ucfirst($type->name) . ' \'' . $componentName . '\' from \'' . $path . '\' '
                . 'has been already defined in \'' . self::$paths[$type->name][$componentName] . '\'.'
            );
        }
        self::$paths[$type->name][$componentName] = str_replace('\\', '/', $path);
    }

    /**
     * @inheritdoc
     */
    public function getPaths(ComponentType $type): array
    {
        return self::$paths[$type->name];
    }

    /**
     * @inheritdoc
     */
    public function getPath(ComponentType $type, string $componentName): string|null
    {
        return self::$paths[$type->name][$componentName] ?? null;
    }
}
