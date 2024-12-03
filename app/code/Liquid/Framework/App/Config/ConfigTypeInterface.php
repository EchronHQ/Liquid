<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

interface ConfigTypeInterface
{
    /**
     * Retrieve configuration data.
     *
     * Returns full configuration array in case $path is empty.
     * In case $path is not empty return value can be either array or scalar
     *
     * @param string $path
     * @return array|int|string|bool|null
     */
    public function get(string $path = ''): array|int|string|bool|null;

    /**
     * @return void
     */
    public function clean(): void;
}
