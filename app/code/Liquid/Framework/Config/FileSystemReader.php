<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

/**
 * Read PHP config files
 */
class FileSystemReader
{
    public function __construct(
        protected readonly FileResolverInterface $fileResolver,
        protected readonly string                $fileName
    )
    {

    }

    /**
     * Load configuration
     *
     * @param string $scope
     * @return array
     */
    public function read(string $scope): array
    {

        $fileList = $this->fileResolver->get($this->fileName, $scope);
        if (!count($fileList)) {
            return [];
        }
        return $this->readFiles($fileList);
    }

    private function readFiles(FileIterator $fileList): array
    {
        $configMerger = null;
        foreach ($fileList as $key) {
            $content = $this->readFile($key);
            if ($configMerger === null) {
                $configMerger = $this->createConfigMerger($content);
            } else {
                $configMerger->merge($content);
            }
        }
        $output = [];
        if ($configMerger) {
            $output = $configMerger->getData();
        }
        return $output;
    }

    private function readFile(string $file): array
    {

        $definitions = require $file;

        if (!\is_array($definitions)) {
            throw new \Exception("File $file should return an array of definitions");
        }
        return $definitions;
    }

    private function createConfigMerger(array $contents): ConfigMerger
    {
        return new ConfigMerger($contents);
    }
}
