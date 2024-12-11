<?php
declare(strict_types=1);

namespace Liquid\Content\Model;

enum ScopeType: string
{
    case DEFAULT = 'default';

    case WEBSITE = 'website';
    case GROUP = 'group';
    case SEGMENT = 'segment';
}
