<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Route;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Cache\Type\Config as ConfigCache;
use Liquid\Framework\App\ConfigScope;
use Liquid\Framework\App\Route\Config\RouteConfigReader;
use Liquid\Framework\Serialize\Serializer\Serialize;

class RouteConfig
{
    private array $routes = [];

    public function __construct(
        private readonly RouteConfigReader $reader,
        private readonly AreaList          $areaList,
        private readonly ConfigScope       $scopeConfig,
        private readonly ConfigCache       $cache,
        private readonly Serialize         $serialize,
        private readonly string            $cacheId = 'routes-config'
    )
    {

    }

    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param AreaCode|null $scope
     * @return string
     * @throws \ReflectionException
     */
    public function getRouteFrontName(string $routeId, AreaCode|null $scope = null): string
    {
        $routes = $this->getRoutes($scope);
        return isset($routes[$routeId]) ? $routes[$routeId]['frontName'] : $routeId;
    }

    /**
     * @param AreaCode|null $scope
     * @return Route[]
     * @throws \ReflectionException
     */
    protected function getRoutes(AreaCode|null $scope = null): array
    {
        $scope = $scope ?: $this->scopeConfig->getCurrentScope();

        if (isset($this->routes[$scope->value])) {
            return $this->routes[$scope->value];
        }
        $cacheId = $scope->value . '::' . $this->cacheId;
        $cachedRoutes = $this->cache->load($cacheId);
        if ($cachedRoutes) {
            $cachedRoutes = $this->serialize->unserialize($cachedRoutes);
            if (is_array($cachedRoutes)) {
                $this->routes[$scope->value] = $cachedRoutes;
                // TODO: parse to objects
                return $cachedRoutes;
            }
        }
        $routes = $this->getRoutesByRouterId($this->areaList->getDefaultRouterId($scope), $scope);

        $routesData = $this->serialize->serialize($routes);
        $this->cache->save($routesData, $cacheId);
        $this->routes[$scope->value] = $routes;
        return $routes;
    }

    /**
     * @param string $routerId
     * @param AreaCode $scope
     * @return Route[]
     * @throws \ReflectionException
     */
    protected function getRoutesByRouterId(string $routerId, AreaCode $scope): array
    {
        return $this->reader->read($scope->value);
    }

    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param AreaCode|null $scope
     * @return Route[]
     * @throws \ReflectionException
     */
    public function getActions(string $frontName, AreaCode|null $scope = null): array
    {
        return $this->getRoutes($scope);

//        $modules = [];
//        foreach ($routes as $route => $routeData) {
//            if ($route === $frontName) {
//                foreach ($routeData as $subRouteKey => $subRoute) {
//                    $modules[$subRouteKey] = $subRoute;
//                }
//            }
//        }
//        return array_unique($modules);
    }

}
