<?php
declare(strict_types=1);

namespace Liquid\Framework\Module;

class ModuleData
{
    public string $name;

    public int $order = 999;
    public bool $enabled = true;
    public array $requires = [];

    public array $routes = [];
    public array $viewableEntityRepositories = [];

    public function __construct(public readonly string $code, public readonly string $path)
    {
        $this->name = $this->code;
    }

    public function setSortOrder(int $order): void
    {
        $this->order = $order;
    }
}
