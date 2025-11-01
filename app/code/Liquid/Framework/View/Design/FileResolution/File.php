<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\ObjectManager\ObjectManager;
use Liquid\Framework\View\Design\FileResolution\Types\ResolveTypeInterface;
use Liquid\Framework\View\Design\FileResolution\Types\ResolveTypesPool;
use Liquid\Framework\View\Design\Theme;

class File
{
    public function __construct(
        private readonly ResolveTypesPool $resolveTypesPool,
        private readonly ObjectManager    $objectManager
    )
    {
    }

    public function getFile(AreaCode $area, Theme|null $themeModel, string $file, string|null $moduleId = null): string|null
    {
        return $this->resolve($this->getFallbackType(), $file, $area, $themeModel, null, $moduleId);
    }

    public function resolve(string $type, string $file, AreaCode|null $area = null, Theme|null $theme = null, string|null $locale = null, string|null $module = null)
    {
        $params = [
            'area' => $area,
            'theme' => $theme,
            'locale' => $locale,
        ];
        foreach ($params as $key => $param) {
            if ($param === null) {
                unset($params[$key]);
            }
        }
        if (!empty($module)) {
            $params['module_name'] = $module;
        }
        return $this->resolveFile($this->resolveTypesPool->getRule($type), $file, $params);
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param ResolveTypeInterface $fallbackRule
     * @param string $file
     * @param array $params
     * @return string|null
     * @throws FileSystemException
     */
    protected function resolveFile(ResolveTypeInterface $fallbackRule, string $file, array $params = []): string|null
    {
        $params['file'] = $file;
        foreach ($fallbackRule->getPatternDirs($params) as $dir) {
            $path = "{$dir}/{$file}";
            $dirRead = $this->objectManager->create(DirectoryRead::class, ['path' => $dir]);
            if ($dirRead->isExist($file) && $this->checkFilePathAccess($file, $path)) {
                return $path;
            } else {
                // TODO: show warning?
                // echo $path . '<br/>';
            }
        }
        return null;
    }

    protected function getFallbackType(): string
    {
        return ResolveTypesPool::TYPE_FILE;
    }

    /**
     * Validate the file path to be secured
     *
     * @param string $fileName
     * @param string $filePath
     * @return bool
     */
    private function checkFilePathAccess(string $fileName, string $filePath): bool
    {
        // Check if file name not contains any references '/./', '/../'
        if (!$fileName || \strpos(\str_replace('\\', '/', $fileName), './') === false) {
            return true;
        }

//        $realPath = realpath($filePath);
//
//        $list = $this->objectManager->get(DirectoryList::class);
//        $directoryWeb = $this->objectManager->create(DirectoryRead::class,['path'=>$list->getPath(Path::LIB_WEB) ])
//
//            $fileRead = $this->objectManager->create(FileRead::class);
//
//
//        // Check if file path starts with web lib directory path
//        $absolutePath = $directoryWeb->getAbsolutePath();
//        if ($absolutePath && strpos($fileRead->getAbsolutePath(), $absolutePath) === 0) {
//            return true;
//        }

        throw new \InvalidArgumentException("File path '{$filePath}' is forbidden for security reasons.");
    }
}
