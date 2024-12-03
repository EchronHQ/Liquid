<?php
declare(strict_types=1);

namespace Liquid\Framework\Module\File;

use Liquid\Core\Helper\FileHelper;
use Liquid\Framework\Config\FileIterator;
use Liquid\Framework\Module\ModuleHelper;

class Reader
{
    public function __construct(
        private readonly ModuleHelper $modulesList,
        private readonly FileHelper   $fileHelper,
    )
    {

    }

    /**
     * Go through all modules and find configuration files of active modules.
     *
     * @param string $filename
     * @return FileIterator
     */
    public function getConfigurationFiles(string $filename): FileIterator
    {

        // TODO: store this so we load this 1 time per request
        return $this->getFiles($filename, 'etc');
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $filename
     * @param string|null $subDir
     * @return FileIterator
     */
    private function getFiles(string $filename, string|null $subDir = null): FileIterator
    {
        $result = [];
        foreach ($this->modulesList->getModules() as $moduleData) {
            $file = $moduleData->path . ($subDir === null ? '' : '/' . $subDir) . '/' . $filename;

            if ($this->fileHelper->fileExist($file)) {
                $result[] = $file;
            }
        }
        return new FileIterator($result);
    }
}
