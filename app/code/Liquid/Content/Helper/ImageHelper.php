<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Asset\AssetSizeInstructionCrop;
use Liquid\Content\Model\Asset\AssetSizeInstructionFilter;
use Liquid\Content\Model\Asset\AssetSizeInstructionType;
use Liquid\Core\Helper\FileHelper;

readonly class ImageHelper
{
    public function __construct(
        private FileHelper $fileHelper
    )
    {
    }


    /**
     * @param string $source
     * @param string $destination
     * @param AssetSizeInstruction $sizeInstruction
     * @return array{width:int,height:int}
     * @throws ImageResizeException
     */
    public function resize(string $source, string $destination, AssetSizeInstruction $sizeInstruction, bool $showDebugInformation = false): array
    {
        if (!$this->fileHelper->fileExist($source)) {
            throw new \Exception('Unable to resize image: source file "' . $source . '" does not exists');
        }
        try {

            $image = new ImageResize($source);
        } catch (\Throwable $ex) {
            throw new \Exception('Unable to resize image: `' . $source . '` ' . $ex->getMessage());
        }


        if ($sizeInstruction->maxWidth !== null && $sizeInstruction->maxHeight !== null) {
            if ($sizeInstruction->crop === AssetSizeInstructionCrop::None) {
                $image->resizeToBestFit($sizeInstruction->maxWidth, $sizeInstruction->maxHeight);
            } else {
                $intPosition = null;
                switch ($sizeInstruction->crop) {
                    case AssetSizeInstructionCrop::Center:
                        $intPosition = ImageResize::CROPCENTER;
                        break;
                    case AssetSizeInstructionCrop::Top:
                        $intPosition = ImageResize::CROPTOP;
                        break;
                    case AssetSizeInstructionCrop::Left:
                        $intPosition = ImageResize::CROPLEFT;
                        break;
                    case AssetSizeInstructionCrop::Bottom:
                        $intPosition = ImageResize::CROPBOTTOM;
                        break;
                    case AssetSizeInstructionCrop::Right:
                        $intPosition = ImageResize::CROPRIGHT;
                        break;
                }

                $image->crop($sizeInstruction->maxWidth, $sizeInstruction->maxHeight, false, $intPosition);
            }
        }

        foreach ($sizeInstruction->getFilters() as $filter) {
            switch ($filter) {
                case AssetSizeInstructionFilter::GreyScale:
                    $image->addFilter(function ($imageDesc) {
                        \imagefilter($imageDesc, IMG_FILTER_GRAYSCALE);
                    });
                    break;
                case AssetSizeInstructionFilter::Blur:
                    $image->addFilter(function ($imageDesc) {
                        imagefilter($imageDesc, IMG_FILTER_GAUSSIAN_BLUR);
                    });
                    break;
                default:
                    throw new \Exception('Unknown filter');
            }
        }


        $convertToType = null;
        $qualityForType = $sizeInstruction->quality;
        if ($sizeInstruction->convert !== null) {
            switch ($sizeInstruction->convert) {
                case AssetSizeInstructionType::GIF:
                    $convertToType = \IMAGETYPE_GIF;
                    break;
                case AssetSizeInstructionType::JPG:
                    $convertToType = \IMAGETYPE_JPEG;
                    break;
                case AssetSizeInstructionType::PNG:
                    $convertToType = \IMAGETYPE_PNG;
                    // Between 0 (no compression) and 9
                    $qualityForType = round($sizeInstruction->quality / 100 * 9);
                    break;
                case AssetSizeInstructionType::WebP:
                    $convertToType = \IMAGETYPE_WEBP;
                    // Percentage
                    break;
                case AssetSizeInstructionType::SVG:
                    throw new \Exception('Conversion to SVG is not supported');
                default:
                    throw new \Exception('Unknown convert image type');
            }
        }
        $result = $image->save($destination, $convertToType, $qualityForType);


        if ($showDebugInformation) {
            $this->addDebugInformation($destination, $result);
        }
        return ['width' => $result->getDestWidth(), 'height' => $result->getDestHeight()];
    }

    private function addDebugInformation(string $destination, ImageResize $result): void
    {

        $image = null;
        $type = AssetSizeInstructionType::fromFile($destination);
        if ($type === AssetSizeInstructionType::PNG) {
            $image = \imagecreatefrompng($destination);
        } elseif ($type === AssetSizeInstructionType::WebP) {
            $image = \imagecreatefromwebp($destination);
        }
        if ($image !== null) {
            $black = \imagecolorallocate($image, 0, 0, 0);
            $white = \imagecolorallocate($image, 255, 255, 255);

            $string = '[' . $result->getDestWidth() . 'x' . $result->getDestHeight() . ' ' . $type->value . '] x';

// TODO: is this path correct? this might need to go to the vendor dir and default liquid theme
            // Don't add image resolve because of circular dependencies
            // $font = $this->resolver->getPubPath() . 'frontend/asset/font/Gilroy/Gilroy-Regular.ttf';
            $font = '';
            if (!file_exists($font)) {
                die('nope');
            }
            //                $fontReference = imageloadfont($font);
            $fontSize = 50;
            $x = 105;
            $y = 105;

            $padding = 10;


            $textBox = \imagettfbbox($fontSize, 0, $font, $string);

            $text_width = $textBox[4] - $textBox[6];
            $text_height = $textBox[3] - $textBox[5];


            \imagefilledrectangle($image, $x, $y, $text_width + (2 * $padding) + $x, $text_height + (2 * $padding) + $x, $white);


            \imagettftext($image, $fontSize, 0, $x + $padding, $text_height + $y + $padding, $black, $font, $string);
            //     imagestring($image, $font, $x + 5, $y + 5, $string, $black);

        }


        if ($type === AssetSizeInstructionType::PNG) {
            \imagepng($image, $destination);
        } elseif ($type === AssetSizeInstructionType::WebP) {
            \imagewebp($image, $destination);
        }


    }

    public function getResizedFileName(string $source, AssetSizeInstruction $sizeInstruction): string
    {
        $fileName = pathinfo($source, \PATHINFO_FILENAME);
        $ext = pathinfo($source, PATHINFO_EXTENSION);

        $arrHash = [];
        if ($sizeInstruction->maxWidth !== null && $sizeInstruction->maxHeight !== null) {
            $arrHash[] = ($sizeInstruction->maxWidth ?? '') . 'x' . ($sizeInstruction->maxHeight ?? '');
        }


        if ($sizeInstruction->crop !== AssetSizeInstructionCrop::None) {
            $arrHash[] = 'c' . substr($sizeInstruction->crop->value, 0, 1);
        }


        if ($sizeInstruction->quality !== AssetSizeInstruction::QUALITY_DEFAULT) {
            $arrHash[] = 'q:' . $sizeInstruction->quality;
        }

        foreach ($sizeInstruction->getFilters() as $filter) {
            switch ($filter) {
                case AssetSizeInstructionFilter::GreyScale:
                    $arrHash[] = 'gs';
                    break;
                case AssetSizeInstructionFilter::Blur:
                    $arrHash[] = 'bl';
                    break;
            }
        }

        if ($sizeInstruction->dpr !== 1) {
            $arrHash[] = 'dpr-' . $sizeInstruction->dpr;
        }


        if ($sizeInstruction->convert !== null) {
            $ext = $sizeInstruction->convert->value;
        }

        // TODO: add crop method + background to hash
        $hash = '';
        if (count($arrHash) > 0) {
            $hash = '-' . str_replace(':', '-', implode('-', $arrHash));
        }

        return $fileName . $hash . '.' . $ext;
    }

    public function canResize(string $source): bool
    {
        $type = AssetSizeInstructionType::fromFile($source);
        return $type !== AssetSizeInstructionType::SVG && $type !== AssetSizeInstructionType::WebP;
    }
}
