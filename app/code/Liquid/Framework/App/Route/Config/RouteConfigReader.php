<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Route\Config;

use Liquid\Framework\App\Route\Attribute\Route as ActionClassAttribute;
use Liquid\Framework\App\Route\Route;
use Liquid\Framework\Config\Reader\AttributeConfigReader;
use Liquid\Framework\Module\File\Dir;
use Liquid\Framework\Module\ModuleHelper;
use Psr\Log\LoggerInterface;

class RouteConfigReader extends AttributeConfigReader
{
    public function __construct(
        ModuleHelper    $modulesList,
        Dir             $moduleDir,
        LoggerInterface $logger,
    )
    {
        parent::__construct(
            $modulesList,
            $moduleDir,
            $logger,
            ActionClassAttribute::class,
            Dir::MODULE_CONTROLLER_DIR
        );
    }

    /**
     * @return Route[][]
     * @throws \ReflectionException
     */
    public function read(string|null $scope = null): array
    {
        /** @var ActionClassAttribute[] $values */
        $values = parent::read($scope);

        $routes = [];
        foreach ($values as $class => $value) {
            $route = new Route();
            $route->class = $class;
            $route->path = $this->formatRoutePath($value->getPath());
            $route->methods = $value->getMethods();
            $routes[$value->getRouterId()][] = $route;
        }
        return $routes;
    }

    /**
     * Formatting:
     * - Make sure path never starts with a slash
     *
     * @param string $input
     * @return string
     */
    private function formatRoutePath(string $input): string
    {
        return ltrim($input, '/');
    }
}
