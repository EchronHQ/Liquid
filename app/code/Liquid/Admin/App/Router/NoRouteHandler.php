<?php
declare(strict_types=1);

namespace Liquid\Admin\App\Router;

use Liquid\Admin\App\Area\FrontNameResolver;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Router\NoRouteHandlerInterface;

class NoRouteHandler implements NoRouteHandlerInterface
{

    public function __construct(
        private readonly FrontNameResolver $frontNameResolver,
        // private \Liquid\Framework\App\Route\ConfigInterface $routeConfig
    )
    {

    }

    public function process(Request $request): bool
    {
        $requestPathParams = \explode('/', \trim($request->getPathInfo(), '/'));
        $areaFrontName = \array_shift($requestPathParams);

        if ($areaFrontName === $this->frontNameResolver->getFrontName(true)) {
//            $moduleName = $this->routeConfig->getRouteFrontName('adminhtml');
//            $actionNamespace = 'noroute';
//            $actionName = 'index';
//            $request->setModuleName($moduleName)->setControllerName($actionNamespace)->setActionName($actionName);
            $request->setPathInfo('admin/noroute/index');
            return true;
        }
        return false;
    }
}
