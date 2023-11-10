<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Echron\Tools\StringHelper;
use Liquid\Core\Helper\Resolver;

class FrontendFileHelper
{
    private Resolver $resolver;

//    private array|null $scripts = null;


    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getFilePath(string $fileKey): string|null
    {
//        if ($this->scripts === null) {
//            $frontendDeployPath = $this->resolver->getFrontendFilePath('');
//            $this->scripts = $this->getFiles($frontendDeployPath);
//        }
        return $this->resolver->getFrontendFilePath($fileKey);

//        if (\array_key_exists($fileKey, $this->scripts)) {
//            return $this->scripts[$fileKey]['path'];
//        }
//        return null;
    }

    public function getUrlByFileName(string $fileName): string
    {
        if (StringHelper::startsWith($fileName, 'https://') || StringHelper::startsWith($fileName, 'http://')) {
            return $fileName;
        }
        $filePath = $this->getFilePath($fileName);

        if ($filePath === null) {
            throw new \Exception('Unable to load file "' . $fileName . '"');
        }

        $url = $this->resolver->getFrontendFileUrl($filePath);
        if ($url === null) {
            throw new \Exception('Url not found for file `' . $filePath . '`');
        }
        return $url;
    }

    private function getKeyByFileName(string $file): string
    {

        $fileName = pathinfo($file, \PATHINFO_BASENAME);
        $extension = pathinfo($file, \PATHINFO_EXTENSION);


        $x = \strtok($fileName, '_');

        if ($x === $fileName) {
            $key = $fileName;
        } else {
            $key = $x . '.' . $extension;
        }
        return $key;
    }

    private function getFiles(string $frontendDeployPath): array
    {


        $files = [];

        $javascriptFiles = $this->getFilesByExtension($frontendDeployPath . 'js', 'js');
        $styleFiles = $this->getFilesByExtension($frontendDeployPath . 'css', 'css');

        $assetFiles = \array_merge($javascriptFiles, $styleFiles);

        // TODO: remove the frontend deploy paths
        foreach ($assetFiles as $assetFile) {

            $key = $this->getKeyByFileName($assetFile['path']);

            if (\array_key_exists($key, $files)) {
                $currentFileTime = $assetFile['mtime'];
                if ($currentFileTime === false) {
                    $currentFileTime = 0;
                }
                $existingFileTime = $files[$key]['mtime'];
                if ($existingFileTime === false) {
                    $existingFileTime = 0;
                }

                if ($currentFileTime > $existingFileTime) {
                    $files[$key] = $assetFile;
                }
            } else {
                $files[$key] = $assetFile;
            }


        }
        return $files;
    }

    private function getFilesByExtension(string $path, string $extension): array
    {
        $dir = new \RecursiveDirectoryIterator($path);
        $flattened = new \RecursiveIteratorIterator($dir);
        /** @var \SplFileInfo[] $files */
        $files = new \RegexIterator($flattened, '/\.(?:' . $extension . ')$/', \RegexIterator::MATCH);

        $fileList = [];
        foreach ($files as $file) {
            $fileList[] = [
                'path' => $file->getPathname(),
                'mtime' => $file->getMTime(),
            ];
        }
        return $fileList;

    }
}
