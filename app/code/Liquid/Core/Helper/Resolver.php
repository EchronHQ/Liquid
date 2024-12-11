<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Echron\Tools\FileSystem;
use Liquid\Content\Helper\ImageHelper;
use Liquid\Content\Helper\StaticContentHelper;
use Liquid\Content\Helper\ViewableEntity;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Core\Model\FrontendFileUrl;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\Component\ComponentFile;
use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\Filesystem\Path;
use Liquid\Framework\Url;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Use separate classes instead
 */
class Resolver
{
    public const FILE_ID_SEPARATOR = '::';
    private array|null $pageRoutes = null;
    private array $randomImageUsed = [];

    public function __construct(
        private readonly ScopeConfig                 $configuration,
        private readonly FileHelper                  $fileHelper,
        private readonly ImageHelper                 $imageHelper,
        private readonly DirectoryList               $directoryList,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly StaticContentHelper         $staticContentHelper,
        private readonly Url                         $url,
        private readonly ViewableEntity              $viewableEntityHelper,
        private readonly LoggerInterface             $logger
    )
    {
    }

    public function getPageUrl(string $pageIdentifier): string|null
    {
        return $this->viewableEntityHelper->getUrl($pageIdentifier);
    }

    public function getAssetUrl(string $file, AssetSizeInstruction|null $size = null): FrontendFileUrl|null
    {
        $file = \ltrim($file, '\\/');

        if (str_starts_with($file, 'asset/')) {
            $file = substr($file, strlen('asset/'));
        }
        return $this->getFrontendFileImageUrl('asset/' . $file, $size);
    }

    public function getFrontendFileImageUrl(string $file, AssetSizeInstruction|null $size = null, bool $forseResizing = false): FrontendFileUrl|null
    {

        $fileData = ComponentFile::extractModule($file);


        // Test theme
        $themes = $this->componentRegistrar->getPaths(ComponentType::Theme);

        foreach ($themes as $themePath) {


            $fileInTheme = $themePath . '/web/' . $fileData['filePath'];
            if ($this->fileHelper->fileExist($fileInTheme)) {
                return $this->getX($file, $fileInTheme, $size, $forseResizing);
            }
            if ($fileData['moduleId'] !== null) {
                $fileInTheme = $themePath . '/' . $fileData['moduleId'] . '/web/' . $fileData['filePath'];
                if ($this->fileHelper->fileExist($fileInTheme)) {
                    return $this->getX($file, $fileInTheme, $size, $forseResizing);
                }
            }

        }

        if ($fileData['moduleId'] !== null) {
            // TODO: implement further
            $x = $this->componentRegistrar->getPath(ComponentType::Module, $fileData['moduleId']);
        }
//        die('---');


//        $localFilePath = $this->getDiskFilePath($file);
//
//        if (!$this->fileHelper->fileExist($localFilePath)) {
        $this->logger->error('Frontend file "' . $file . '" does not exists', ['locale path' => $file]);
        // TODO: show placeholder
        return null;
//        }

    }

    public function getPath(Path $path, string|null $x = null): string
    {
        $fullPath = $this->directoryList->getPath($path);
        if ($x !== null) {
            // TODO: cleanup if x already starts with slash
            $fullPath = $fullPath . '/' . $x;
        }
        return $fullPath;
    }

    public function getUrlPath(Path $path, string|null $x = null): string
    {
        $siteUrl = $this->url->getBaseUrl(['_type' => Url\UrlType::MEDIA]);
        if ($path !== Path::MEDIA) {
            $siteUrl .= '/' . $this->staticContentHelper->getStaticDeployedVersion();
        }

        // $siteUrl = $this->url->getBaseUrl(['_type' => Url\UrlType::MEDIA]) . '/' . $this->staticContentHelper->getStaticDeployedVersion();
        // var_dump($siteUrl);
//        $siteUrl = $this->configuration->getValue('web/unsecure/base_url');
        //  $fullUrlPath = $this->directoryList->getUrlPath($path);


        if ($x !== null) {
            // TODO: cleanup if x already starts with slash
            $siteUrl .= '/' . $x;
        }
//        return $fullUrlPath;
        return $siteUrl;
    }

    public function getRandomImage(int $width = 250, int $height = 250): string
    {
        $randomImageId = \random_int(1000, 1050);
        while (\in_array($randomImageId, $this->randomImageUsed, true)) {
            $randomImageId = \random_int(1000, 1050);
        }

        $this->randomImageUsed[] = $randomImageId;
        return 'https://picsum.photos/id/' . $randomImageId . '/' . $width . '/' . $height . '?blur=1&random=' . \random_int(0, 999);
    }

    public function getFrontendFileUrl(string $file): string|null
    {
        $file = $this->cleanupPath($file);


        $localFilePath = $this->getDiskFilePath($file);

        if (!$this->fileHelper->fileExist($localFilePath)) {
            $this->logger->error('Frontend file "' . $file . '" does not exists', ['locale path' => $localFilePath]);
            // TODO: show placeholder
            return null;
        }
        return $this->url->getBaseUrl(['_type' => Url\UrlType::STATIC]) . '/' . $this->staticContentHelper->getStaticDeployedVersion() . '/' . $file;
    }

    public function getFrontendFilePath(string $file): string
    {
        // TODO: if path already starts with root dir, return it
        if (str_starts_with($file, '/var/www/liquid')) {
            return $file;
        }
        return $this->getPath(Path::STATIC_VIEW, $this->staticContentHelper->getStaticDeployedVersion() . '/' . $file);
    }

    //ToDo: split this method for static files and images as they return different data

    public function getUrl(string $path = '', SegmentId|null $locale = null): string
    {
        return $this->url->getUrl($path, $locale);
    }

    public function setPageRoutes(array $routes): void
    {
        $this->pageRoutes = $routes;
    }

    public function renderSVG(string $path, bool $allowCache = false): string
    {
        $fullPath = $this->getFrontendFilePath($path);
        if (!$this->fileHelper->fileExist($fullPath)) {
            $this->logger->error('Unable to get file content, file does not exist', ['path' => $fullPath]);
            //            throw new \Exception('Path does not exist: ' . $path);
            return '';
        }
        return $this->fileHelper->getFileContent($fullPath, $allowCache);
    }

    private function getX(string $file, string $localFilePath, AssetSizeInstruction|null $size = null, bool $forseResizing = false): FrontendFileUrl|null
    {

        // Change this to cache/ to add additional depth in pub/media/
        $mediaCacheSuffix = '';

        $cacheLocation = $this->getPath(Path::MEDIA, $mediaCacheSuffix);


        if (!FileSystem::dirExists($cacheLocation)) {
            FileSystem::createDir($cacheLocation);
        }

        $canResize = $this->imageHelper->canResize($localFilePath);
        if ($size !== null && $canResize) {


            $resizedLocalFileName = $this->imageHelper->getResizedFileName($localFilePath, $size);


            $resizedLocalFilePath = $cacheLocation . '/' . $resizedLocalFileName;
            $hasResized = $this->fileHelper->fileExist($resizedLocalFilePath);


            $resizedImageInfo = null;

            if ($hasResized) {
                $resizedImageInfo = $this->fileHelper->getImageDimensions($resizedLocalFilePath);
            }


            if ($forseResizing || !$hasResized) {
                try {
                    $resizedImageInfo = $this->imageHelper->resize($localFilePath, $resizedLocalFilePath, $size);
                    $this->fileHelper->setFileExist($resizedLocalFilePath, true);
                    $hasResized = true;
                } catch (\Throwable $ex) {
                    $this->logger->error('Unable to resize image', ['ex' => $ex->getMessage()]);
                    return null;
                }

            }

            //            if ($resizedImageInfo === null) {
            //                $this->logger->error('Unable to get image dimensions', ['path' => $resizedLocalFilePath]);
            //                return null;
            //            }

            return new FrontendFileUrl($this->getUrlPath(Path::MEDIA, $mediaCacheSuffix . $resizedLocalFileName), $resizedImageInfo['width'], $resizedImageInfo['height']);


        }
        $dimension = ['width' => null, 'height' => null];

        if ($canResize) {
            $dimension2 = $this->fileHelper->getImageDimensions($localFilePath);
            if ($dimension2 === null) {
                $this->logger->error('Unable to get image dimensions', ['path' => $localFilePath]);
            } else {
                $dimension = $dimension2;
            }
        }
        // Copy file to cache location so we can see it!

        $fileDir = $cacheLocation . '/' . dirname($file);

        if (!FileSystem::dirExists($fileDir)) {
            FileSystem::createDir($fileDir);
        }
        $cachedFileLocation = $cacheLocation . '/' . $file;

        // TODO: in dev mode we should test if the file is newer
        if (!$this->fileHelper->fileExist($cachedFileLocation)) {
            $this->logger->info('[Resolver] copy ' . $localFilePath . ' to ' . $cachedFileLocation);

            $this->fileHelper->copyFile($localFilePath, $cachedFileLocation);

            $this->fileHelper->clearFileExists($cachedFileLocation);
        }


        //  die('--');
        return new FrontendFileUrl($this->getUrlPath(Path::MEDIA, $mediaCacheSuffix . $file), $dimension['width'], $dimension['height']);

    }

    private function cleanupPath(string $file): string
    {
        $frontendDeployPath = $this->getFrontendFilePath('');

        $file = \ltrim($file, '\\');
        return \str_replace($frontendDeployPath, '', $file);
    }

    private function getDiskFilePath(string $file): string
    {
        $frontendDeployPath = $this->getFrontendFilePath('');

        $file = \ltrim($file, '\\');
        $file = \str_replace($frontendDeployPath, '', $file);

        return $frontendDeployPath . $file;
    }


}
