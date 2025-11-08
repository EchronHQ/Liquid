<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Request\HttpRequest;

interface NoRouteHandlerInterface
{
    /**
     * Check and process no route request
     */
    public function process(HttpRequest $request): bool;
}
