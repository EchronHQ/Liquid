<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

enum PageStatus
{
    case ACTIVE;
    case DRAFT;
    case REMOVED;

    public static function fromString(string $input): self
    {
        switch (\strtolower($input)) {
            case 'active':
                return self::ACTIVE;
                // no break
            case 'draft':
                return self::DRAFT;
            case 'removed':
                return self::REMOVED;
        }
        throw new \Exception('Page status "' . $input . '" not recognized');
    }
}
