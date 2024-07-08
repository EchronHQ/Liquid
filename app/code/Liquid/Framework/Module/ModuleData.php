<?php
declare(strict_types=1);

namespace Liquid\Framework\Module;

class ModuleData
{
    public int $order = 999;
    public array $routes = [];
    public array $viewableEntityRepositories = [];

    public function __construct(public readonly string $name)
    {

    }
}
