<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Asset;

enum AssetSizeInstructionType: string
{
    case WebP = 'webp';
    case SVG = 'svg';
    case JPG = 'jpg';
    case PNG = 'png';
    case GIF = 'gif';

    public static function fromFile(string $file): self
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'png':
                return self::PNG;
            case 'svg':
                return self::SVG;
            case 'jpg':
            case 'jpeg':
                return self::JPG;
            case 'webp':
                return self::WebP;
        }

        throw new \Exception('Unknown image type for file "' . $file . '"');
    }
}
