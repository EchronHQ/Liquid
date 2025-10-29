<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Source;

use Liquid\Framework\App\Config\ConfigSourceInterface;

/**
 * The source with previously imported configuration. (not in use atm)
 */
class InitialConfigSource implements ConfigSourceInterface
{
    public function get(string $path = ''): string|array
    {
        die('Not implemented');
        // TODO: implement this
        return [];
    }
}
