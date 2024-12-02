<?php
declare(strict_types=1);

namespace Liquid\Framework\Module\File;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;

class Dir
{
    /**#
     * Directories within modules
     */
    public const MODULE_ETC_DIR = 'etc';
    public const MODULE_VIEW_DIR = 'view';

    public const MODULE_CONTROLLER_DIR = 'Controller';
    public const MODULE_OBSERVER_DIR = 'Observer';

    private const ALLOWED_DIR_TYPES = [
        self::MODULE_ETC_DIR,
        self::MODULE_VIEW_DIR,
        self::MODULE_CONTROLLER_DIR,
        self::MODULE_OBSERVER_DIR,
    ];

    public function __construct(private readonly ComponentRegistrarInterface $componentRegistrar)
    {
    }

    /**
     * Retrieve full path to a directory of certain type within a module
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $type Type of module's directory to retrieve
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir(string $moduleName, string $type = ''): string
    {
        $path = $this->componentRegistrar->getPath(ComponentType::Module, $moduleName);
        // An empty $type means it's getting the directory of the module itself.
        if (empty($type) && !isset($path)) {
            // Note: do not throw \LogicException, as it would break backwards-compatibility.
            throw new \InvalidArgumentException("Module '$moduleName' is not correctly registered.");
        }

        if ($type) {
            if (!in_array($type, self::ALLOWED_DIR_TYPES)) {
                throw new \InvalidArgumentException("Directory type '{$type}' is not recognized.");
            }
            $path .= '/' . $type;
        }

        return $path;
    }
}
