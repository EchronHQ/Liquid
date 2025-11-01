<?php
declare(strict_types=1);

namespace Liquid\Content\ViewModel;

use Liquid\Content\Block\Html\Picture;
use Liquid\Content\Helper\ImageHelper;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Helper\RandomImageHelper;
use Liquid\Content\Helper\ViewableEntity;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Config\SegmentConfigInterface;
use Liquid\Framework\Escaper;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\Url;
use Liquid\Framework\View\Element\ArgumentInterface;
use Liquid\Framework\View\Layout\Layout;
use Psr\Log\LoggerInterface;

class BaseViewModel implements ArgumentInterface
{


    public function __construct(
        private readonly Resolver               $resolver,
        private readonly Url                    $url,
        private readonly LocaleHelper           $localeHelper,
        private readonly ObjectManagerInterface $objectManager,
        private readonly Layout                 $layout,
        private readonly Escaper                $escaper,
        private readonly ViewableEntity         $viewableEntityHelper,
        private readonly ImageHelper            $imageHelper,
        private readonly RandomImageHelper      $randomImageHelper,
        private readonly SegmentConfigInterface $config,
        private readonly LoggerInterface        $logger,
    )
    {
    }

    /**
     * @deprecated
     * The resolver is deprecated
     */
    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function getEntityUrl(string $entityIdentifier): string
    {
        $url = $this->viewableEntityHelper->getUrl($entityIdentifier);
        if ($url === null) {
            $this->logger->notice('Unable to get entity url for `' . $entityIdentifier . '`');
            return '';
        }
        return $url;
    }

    public function getConfig(): SegmentConfigInterface
    {
        return $this->config;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getObjectManager(): ObjectManagerInterface
    {
        return $this->objectManager;
    }

    public function translate(string $input): string
    {
        return $this->localeHelper->translate($input);
    }

    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }

    public function getViewableEntityHelper(): ViewableEntity
    {
        return $this->viewableEntityHelper;
    }

    public function renderLazyLoad(string $assetFile, string $alt = '', AssetSizeInstruction|array|null $sizeInstruction = null, bool $lazyLoad = true): string
    {
        if ($assetFile === RandomImageHelper::RANDOM_IMAGE) {
            $assetFile = $this->randomImageHelper->getRandomFeatureImage();
        }
        $picture = $this->layout->createBlock(Picture::class);
        \assert($picture instanceof Picture);
        $picture->setSrc($assetFile, $alt);
        if ($sizeInstruction !== null) {
            if (\is_array($sizeInstruction)) {
                $sizeInstruction = new AssetSizeInstruction($sizeInstruction['width'], $sizeInstruction['height']);
            }
            $picture->setSize($sizeInstruction);
        }
        $picture->lazyLoad = $lazyLoad;
        return $picture->toHtml();
    }

    public function renderSVG(string $svg): string
    {
        $path = $this->resolver->getAssetUrl($svg);
        if ($path === null) {
            $this->logger->error('SVG not found `' . $svg . '`');
            return '';
        }
        if (\file_exists($path->path)) {
            return \file_get_contents($path->path);
        }
        $this->logger->error('SVG not found `' . $path->path . '`');
        return '';
        // return $this->resolver->renderSVG($path, false);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
