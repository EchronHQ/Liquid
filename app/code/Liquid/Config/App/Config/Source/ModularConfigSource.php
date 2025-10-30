<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Source;

use Liquid\Framework\App\Config\ConfigSourceInterface;
use Liquid\Framework\App\Config\Initial\Reader;
use Liquid\Framework\DataObject;

class ModularConfigSource implements ConfigSourceInterface
{

    public function __construct(
        private readonly Reader $reader
    )
    {
    }

    /**
     * Get initial data
     *
     * @param string $path Format is scope type and scope code separated by slash: e.g. "type/code"
     * @return array
     */
    public function get(string $path = ''): array
    {
        $data = new DataObject($this->reader->read());
        if ($path !== '') {
            $path = '/' . $path;
        }
        return $data->getData('data' . $path) ?: [];
    }
}
