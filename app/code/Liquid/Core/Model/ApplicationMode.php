<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

enum ApplicationMode
{
    case DEVELOP;
    case PRODUCTION;
}
