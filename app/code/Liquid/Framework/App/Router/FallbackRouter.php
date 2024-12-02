<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\ForwardAction;
use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\Controller\Result\Forward;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class FallbackRouter implements RouterInterface
{
    public function __construct(
        private readonly SegmentConfig          $config,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function match(Request $request): AbstractAction|null
    {
//        foreach ($this->noRouteHandlerList->getHandlers() as $noRouteHandler) {
//            if ($noRouteHandler->process($request)) {
//                break;
//            }
//        }
        $this->processNoRoute($request);

        return $this->objectManager->create(ForwardAction::class, [
            'forward' => $this->objectManager->create(Forward::class),
        ]);
    }

    /**
     * This is the method for the frontend, we could inject the no route handler with a list so every scope can have a different no route handler
     *
     * @param Request $request
     * @return bool
     */
    private function processNoRoute(Request $request): bool
    {
        $noRoutePath = $this->config->getValue('web/default/no_route', 'default');

        if ($noRoutePath) {
            $noRoute = explode('/', $noRoutePath);
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
