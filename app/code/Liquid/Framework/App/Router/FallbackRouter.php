<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Action\ForwardAction;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\Controller\Result\Forward;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class FallbackRouter implements RouterInterface
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly NoRouteHandlerList     $noRouteHandlerList
    )
    {

    }

    public function match(Request $request): ActionInterface|null
    {
        foreach ($this->noRouteHandlerList->getHandlers() as $noRouteHandler) {
            if ($noRouteHandler->process($request)) {
                break;
            }
        }

        return $this->objectManager->create(ForwardAction::class, [
            'forward' => $this->objectManager->create(Forward::class),
        ]);
    }
}
