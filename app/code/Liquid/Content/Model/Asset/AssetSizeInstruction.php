<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Asset;

class AssetSizeInstruction
{
    public const QUALITY_DEFAULT = 85;
    public int|null $maxWidth = null;
    public int|null $maxHeight = null;

    public int $dpr = 1;

    public AssetSizeInstructionCrop $crop = AssetSizeInstructionCrop::None;
    public AssetSizeInstructionType|null $convert = null;

    public int $quality;
    private array $filters = [];

    /**
     * null = transparent | rgb color
     * @var string|null
     */
    public string|null $background = null;

    public function __construct(int|null $width = null, int|null $height = null, AssetSizeInstructionCrop|null $crop = AssetSizeInstructionCrop::None, int|null $quality = self::QUALITY_DEFAULT)
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        $this->crop = $crop;
        $this->quality = $quality;
    }

    public function addFilter(AssetSizeInstructionFilter $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @return AssetSizeInstructionFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public static function create(AssetSizeInstruction|null $size): self
    {
        if ($size === null) {
            return new AssetSizeInstruction(null, null);
        }

        $newSize = new AssetSizeInstruction($size->maxWidth, $size->maxHeight, $size->crop, $size->quality);
        $newSize->dpr = $size->dpr;
        foreach ($size->getFilters() as $filter) {
            $newSize->addFilter($filter);
        }

        return $newSize;
    }
}
