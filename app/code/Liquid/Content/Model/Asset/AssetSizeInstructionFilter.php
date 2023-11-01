<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Asset;

enum AssetSizeInstructionFilter: string
{
    case GreyScale = 'greyscale';

    case Blur = 'blur';
}
