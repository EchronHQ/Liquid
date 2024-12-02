<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File;

class FileList
{
    /**
     * Array of files
     *
     * @var File[]
     */
    protected array $files = [];

    public function __construct()
    {

    }

    /**
     * Retrieve all view file instances
     *
     * @return File[]
     */
    public function getAll(): array
    {
        return array_values($this->files);
    }

    /**
     * Add view file instances to the list, preventing identity coincidence
     *
     * @param File[] $files
     * @return void
     * @throws \LogicException
     */
    public function add(array $files): void
    {
        foreach ($files as $file) {
            $identifier = $file->getFileIdentifier();
            if (array_key_exists($identifier, $this->files)) {
                $filename = $this->files[$identifier]->getFilename();
                throw new \LogicException(
                    "View file '{$file->getFilename()}' is indistinguishable from the file '{$filename}'."
                );
            }
            $this->files[$identifier] = $file;
        }
    }

    /**
     * Replace already added view files with specified ones, checking for identity match
     *
     * @param File[] $files
     * @return void
     */
    public function replace(array $files)
    {
        $this->files = $this->collate($files, $this->files);
    }

    /**
     * @param File[] $files
     * @param File[] $filesOrigin
     * @return File[]
     */
    private function collate(array $files, array $filesOrigin): array
    {
        foreach ($files as $file) {
            $identifier = $file->getFileIdentifier();
            if (!array_key_exists($identifier, $filesOrigin)) {
                throw new \LogicException(
                    "Overriding view file '{$file->getFilename()}' does not match to any of the files."
                );
            }
            $filesOrigin[$identifier] = $file;
        }
        return $filesOrigin;
    }
}
