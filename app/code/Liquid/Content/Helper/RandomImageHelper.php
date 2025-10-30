<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

class RandomImageHelper
{
    public const string RANDOM_IMAGE = ':random:';

    private array $randomImages;
    private int $randomImageIndex;

    public function __construct()
    {
        $this->randomImages = [
            'asset/features/random1.jpg',
            'asset/features/random2.jpg',
            'asset/features/random3.jpg',
            'asset/features/random4.jpg',
            'asset/features/random5.jpg',
            'asset/features/random6.jpg',
            'asset/features/random7.jpg',
            'asset/features/random8.jpg',
        ];
        // TODO: can we determine random based on the current request?
        $this->randomImageIndex = \random_int(0, \count($this->randomImages) - 1);
    }


    public function getRandomFeatureImage(): string
    {
        $image = $this->randomImages[$this->randomImageIndex];

        ++$this->randomImageIndex;
        if ($this->randomImageIndex > \count($this->randomImages) - 1) {
            $this->randomImageIndex = 0;
        }

        return $image;
    }
}
