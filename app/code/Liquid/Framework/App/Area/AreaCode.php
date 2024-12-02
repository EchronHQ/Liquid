<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Area;

enum AreaCode: string
{
    case Global = 'global';

    case Frontend = 'frontend';
    case Admin = 'admin';
    case Cli = 'cli';
}
