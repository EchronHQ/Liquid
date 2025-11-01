<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Config\SegmentConfigInterface;
use Liquid\Framework\App\Request\Request;

class NoRouteHandler implements NoRouteHandlerInterface
{
    public function __construct(
        private readonly SegmentConfigInterface $config,
    )
    {

    }

    /**
     * This is the method for the frontend, we could inject the no route handler with a list so every scope can have a different no route handler
     *
     * @param Request $request
     * @return bool
     */
    public function process(Request $request): bool
    {
        $noRoutePath = $this->config->getValue('web/default/no_route', null);

        if ($noRoutePath) {
            $noRoute = \explode('/', $noRoutePath);
        } else {
            $noRoute = [];
        }

        $moduleName = isset($noRoute[0]) ? $noRoute[0] : 'core';
        $actionPath = isset($noRoute[1]) ? $noRoute[1] : 'index';
        $actionName = isset($noRoute[2]) ? $noRoute[2] : 'index';

        $request->setPathInfo('content/noroute/index');
        // $request->setModuleName($moduleName)->setControllerName($actionPath)->setActionName($actionName);

        return true;
    }
}
