<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Liquid\Content\Helper\ImageHelper;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Locale;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\FrontendFileUrl;
use Echron\Tools\FileSystem;
use Psr\Log\LoggerInterface;

class Resolver
{
    private array|null $pageRoutes = null;
    private array $randomImageUsed = [];

    public function __construct(
        private readonly AppConfig       $configuration,
        private readonly FileHelper      $fileHelper,
        private readonly ImageHelper     $imageHelper,
        private readonly LoggerInterface $logger
    ) {
    }

    private function isUrl(string $url): bool
    {
        return \str_starts_with($url, 'http://') || \str_starts_with($url, 'https://');
    }

    public function getUrl(string $path = '', Locale|null $locale = null): string
    {
        if ($this->isUrl($path)) {
            return $path;
        }

        if ($locale === null && $this->configuration->isLocaleDefined()) {
            $locale = $this->configuration->getLocale();
        }
        if ($locale !== null && $locale->code === 'en-uk') {
            $locale = null;
        }

        // TODO: is there a way to check if the url exist?
        $path = \ltrim($path, '/');
        if ($locale === null) {
            return $this->configuration->getValueString('site_url') . $path;
        }
        return $this->configuration->getValueString('site_url') . $locale->code . '/' . $path;


    }

    public function getPageUrl(string $pageIdentifier): string|null
    {
        if (\is_null($this->pageRoutes)) {
            $this->logger->error('[Resolver] Unable to get page url: page routes not defined');
            return null;
        }
        if ($pageIdentifier === 'home') {
            return $this->getUrl();
        }
        if ($pageIdentifier === 'docs') {
            return $this->configuration->getValueString('documentation_url');
        }
        if ($pageIdentifier === 'docs/api') {
            return $this->configuration->getValueString('api_reference_url');
        }
        if ($pageIdentifier === 'status') {
            return $this->configuration->getValueString('status_url');
        }
        if ($pageIdentifier === 'app') {
            return $this->configuration->getValueString('app_url');
        }


        if (\array_key_exists($pageIdentifier, $this->pageRoutes) && !\is_null($this->pageRoutes[$pageIdentifier])) {
            return $this->getUrl($this->pageRoutes[$pageIdentifier]);
        }
        $debugData = [
            'registered routes' => $this->pageRoutes,
            'exists' => \array_key_exists($pageIdentifier, $this->pageRoutes),

        ];
        if (\array_key_exists($pageIdentifier, $this->pageRoutes)) {
            $debugData['notnull'] = !\is_null($this->pageRoutes[$pageIdentifier]);
        }

        $this->logger->error('[Resolver] Unable to get page url: page "' . $pageIdentifier . '" not found', $debugData);


        return null;
    }

    public function getAssetUrl(string $file, AssetSizeInstruction|null $size = null): FrontendFileUrl|null
    {
        $file = \ltrim($file, '\\/');

        if (str_starts_with($file, 'asset/')) {
            $file = substr($file, strlen('asset/'));
        }
        return $this->getFrontendFileImageUrl('asset/' . $file, $size);
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


    private function getDiskFilePath(string $file): string
    {
        $frontendDeployPath = $this->getFrontendFilePath('');

        $file = \ltrim($file, '\\');
        $file = \str_replace($frontendDeployPath, '', $file);

        return $frontendDeployPath . $file;
    }

    private function cleanupPath(string $file): string
    {
        $frontendDeployPath = $this->getFrontendFilePath('');

        $file = \ltrim($file, '\\');
        $file = \str_replace($frontendDeployPath, '', $file);

        return $file;
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
        return $this->configuration->getValueString('site_url') . 'pub/frontend/' . $file;
    }


    //ToDo: split this method for static files and images as they return different data
    public function getFrontendFileImageUrl(string $file, AssetSizeInstruction|null $size = null, bool $forseResizing = false): FrontendFileUrl|null
    {


        // Validates if image
        // $type = AssetSizeInstructionType::fromFile($file);

        $localFilePath = $this->getDiskFilePath($file);

        if (!$this->fileHelper->fileExist($localFilePath)) {
            $this->logger->error('Frontend file "' . $file . '" does not exists', ['locale path' => $localFilePath]);
            // TODO: show placeholder
            return null;
        }

        $canResize = $this->imageHelper->canResize($localFilePath);
        if ($size !== null && $canResize) {


            $resizedLocalFileName = $this->imageHelper->getResizedFileName($localFilePath, $size);
            $cacheLocation = \ROOT . 'pub/frontend/cache/';
            if (!FileSystem::dirExists($cacheLocation)) {
                FileSystem::createDir($cacheLocation);
            }

            $resizedLocalFilePath = $cacheLocation . $resizedLocalFileName;
            $hasResized = $this->fileHelper->fileExist($resizedLocalFilePath, false);


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
            return new FrontendFileUrl($this->configuration->getValueString('site_url') . 'pub/frontend/cache/' . $resizedLocalFileName, $resizedImageInfo['width'], $resizedImageInfo['height']);


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

        return new FrontendFileUrl($this->configuration->getValueString('site_url') . 'pub/frontend/' . $file, $dimension['width'], $dimension['height']);
    }

    public function getFrontendFilePath(string $file): string
    {
        return \ROOT . 'pub/frontend/' . $file;
    }


    public function setPageRoutes(array $routes): void
    {
        $this->pageRoutes = $routes;
    }


}
