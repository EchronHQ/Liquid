<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem;

enum Path
{
    case ROOT;
    case APP;
    case CONFIG;

    case PUB;
    case MEDIA;
    case STATIC_VIEW;

    case SYS_TMP;


}
