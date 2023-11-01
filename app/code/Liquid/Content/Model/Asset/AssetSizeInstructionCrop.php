<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Asset;

enum AssetSizeInstructionCrop: string
{
    case None = 'none';
    case Center = 'center';
    case Top = 'top';
    case Bottom = 'bottom';
    case Left = 'left';
    case Right = 'right';

}
