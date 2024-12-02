<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

interface FileResolverInterface
{
    /**
     * Retrieve the list of configuration files with given name that relate to specified scope
     *
     * @param string $filename
     * @param string|null $scope
     * @return FileIterator
     */
    public function get(string $filename, string|null $scope = null): FileIterator;
}
