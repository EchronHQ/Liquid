<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Route\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Route
{
    private string $path;
    private string $name;
    private string $routerId;
    private array $methods;

    /**
     * @param string $path The route path (i.e. "/user/login")
     * @param string|null $name
     * @param string $routerId The router to connect this route to (standard, admin)
     * @param array $methods The list of HTTP methods allowed by this route (GET, POST, PUT, DELETE)
     */
    public function __construct(
        string $path,
        string $name = null,
        string $routerId = 'standard',
        array  $methods = [],
    )
    {
        $this->path = $path;
        $this->name = $name ?? $path;
        $this->routerId = $routerId;
        $this->methods = $methods;
    }

    public function getRouterId(): string
    {
        return $this->routerId;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}
