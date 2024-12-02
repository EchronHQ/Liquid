<?php
declare(strict_types=1);

namespace Liquid\Framework\Config\Reader;

interface ConfigReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read(string|null $scope = null): array;
}
