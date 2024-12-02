<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Html;

use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Asset\AssetSizeInstructionType;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\FrontendFileUrl;
use Liquid\Framework\App\State;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\TemplateEngine;
use Psr\Log\LoggerInterface;

class Picture extends Template
{
    public bool $lazyLoad = true;
    public FrontendFileUrl|null $lowQuality = null;
    protected string|null $template = 'Liquid_Content::html/picture.phtml';
    private string|null $imageSrc = null;
    private string|null $alt = null;
    private FrontendFileUrl|null $default = null;
    /** @var array{type:string, sizes:array}[]|null */
    private array|null $sizes = null;
    private AssetSizeInstruction|null $size = null;


    public function __construct(
        Layout                    $layout,
        TemplateFileResolver      $templateFileResolver,
        TemplateEngine            $templateEngine,
        State                     $appState,
        Profiler                  $profiler,
        FileHelper                $fileHelper,
        LoggerInterface           $logger,
        private readonly Resolver $resolver,
        string                    $nameInLayout = '',
        array                     $data = []
    )
    {
        parent::__construct($layout, $templateFileResolver, $templateEngine, $appState, $profiler, $fileHelper, $logger, $nameInLayout, $data);
    }

    public function setSrc(string $imageSrc, string $alt = ''): void
    {
        $this->imageSrc = $imageSrc;
        $this->alt = $alt;


    }

    public function setSize(AssetSizeInstruction $sizeInstruction): void
    {
        $this->size = $sizeInstruction;
    }

    public function getDefaultSrc(): FrontendFileUrl|null
    {
        return $this->default;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function hasFormats(): bool
    {
        return $this->sizes !== null;
    }

    public function getFormats(): array
    {
        return $this->sizes;
    }

    public function toHtml(): string
    {

        $default = $this->resolver->getAssetUrl($this->imageSrc, $this->size);

        if ($default === null) {
            // TODO: render something...
            return '[No default found]';
        }
        $this->default = $default;

        $type = AssetSizeInstructionType::fromFile($this->imageSrc);
        if ($type !== AssetSizeInstructionType::SVG) {

            //            $x = AssetSizeInstruction::create($this->size);
            //            $x->quality = 5;
            //            $x->convert = AssetSizeInstructionType::WebP;
            //            $x->maxWidth = 50;
            //            $x->addFilter(AssetSizeInstructionFilter::Blur);
            //
            //            $this->lowQuality = $this->getResolver()->getAssetUrl($this->imageSrc, $x);


            $normal = $this->size;


            $normalWebP = $this->getFormatSrc(AssetSizeInstructionType::WebP, $normal);
            if ($normalWebP === null) {
                throw new \Error('Unable to resize image');
            }
            $small = AssetSizeInstruction::create($this->size);
            $small->maxWidth = (int)($normalWebP->width / 4);
            $small->maxHeight = (int)($normalWebP->height / 4);


            $medium = AssetSizeInstruction::create($this->size);
            $medium->maxWidth = (int)($normalWebP->width / 2);
            $medium->maxHeight = (int)($normalWebP->height / 2);

            //768
            $webP = [
                '(max-width: 380px)' => $this->getFormatSrc(AssetSizeInstructionType::WebP, $small),
                '(max-width: 1199px)' => $this->getFormatSrc(AssetSizeInstructionType::WebP, $medium),
                '(min-width: 1200px)' => $normalWebP,
            ];
            $png = [
                '(max-width: 380px)' => $this->getFormatSrc(AssetSizeInstructionType::PNG, $small),
                '(max-width: 1199px)' => $this->getFormatSrc(AssetSizeInstructionType::PNG, $medium),
                '(min-width: 1200px)' => $this->getFormatSrc(AssetSizeInstructionType::PNG, $normal),
            ];


            $this->sizes = [
                ['type' => 'image/webp', 'sizes' => $webP],
                ['type' => 'image/png', 'sizes' => $png],
            ];
        }
        return parent::toHtml();
    }

    private function getFormatSrc(AssetSizeInstructionType $forceType, AssetSizeInstruction|null $size = null): FrontendFileUrl|null
    {
        $currentExtension = AssetSizeInstructionType::fromFile($this->imageSrc);
        if ($currentExtension === AssetSizeInstructionType::SVG) {
            // Do not provide alternatives for SVG images
            return null;
        }

        $newSize = AssetSizeInstruction::create($size);
        $newSize->convert = $forceType;

        return $this->resolver->getAssetUrl($this->imageSrc, $newSize);


        /**
         * Render images for different viewports (based on CSS breakpoints?)
         */
    }
}
