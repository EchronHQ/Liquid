<?php
declare(strict_types=1);

namespace Liquid\Admin\App;

use Liquid\Config\App\Config\Type\System;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Config\SegmentConfigInterface;

class Config
{
    private array $data = [];

    public function __construct(
        private readonly ScopeConfig $appConfig
    )
    {

    }

    public function getValue(string $path): mixed
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }
        $configPath = SegmentConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }


    public function setValue(string $path, mixed $value): void
    {
        $this->data[$path] = $value;
    }
}
