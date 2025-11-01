<?php
declare(strict_types=1);

namespace Liquid\Framework\View;

use Liquid\Framework\Component\ComponentFile;
use Liquid\Framework\View\Asset\Repository;
use Liquid\Framework\View\Design\FileResolution\TemplateFile;

class ViewFileSystem
{
    public function __construct(
        private readonly Repository   $assetRepository,
        private readonly TemplateFile $templateFileResolution
    )
    {
    }

    /**
     * Get a template file
     *
     * @param string $fileId
     * @param array $params
     * @return string|null
     */
    public function getTemplateFileName(string $fileId, array $params = []): string|null
    {
        $file = ComponentFile::extractModule(self::normalizePath($fileId));
        if ($file['moduleId']) {
            $params['module'] = $file['moduleId'];
        }
        if (!isset($params['module'])) {
            $params['module'] = null;
        }

        $params = $this->assetRepository->updateDesignParams($params);
        return $this->templateFileResolution->getFile($params['area'], $params['themeModel'], $file['filePath'], $params['module']);
    }

    /**
     * TODO: move this method somewhere more general
     * Remove excessive "." and ".." parts from a path
     *
     * For example foo/bar/../file.ext -> foo/file.ext
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        $parts = $path !== null ? \explode('/', $path) : [];
        $result = [];

        foreach ($parts as $part) {
            if ('..' === $part) {
                if (!count($result) || ($result[count($result) - 1] === '..')) {
                    $result[] = $part;
                } else {
                    \array_pop($result);
                }
            } elseif ('.' !== $part) {
                $result[] = $part;
            }
        }
        return \implode('/', $result);
    }
}
