<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

enum AppMode: string
{
    case Develop = 'develop';
    case Production = 'production';
}
