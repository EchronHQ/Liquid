<?php
declare(strict_types=1);

namespace Liquid\Content\Controller;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\HttpRequest;
use Liquid\Framework\App\Router\RouterInterface;

class Router implements RouterInterface
{

    public function match(HttpRequest $request): ActionInterface|null
    {
        return null;
    }
}
