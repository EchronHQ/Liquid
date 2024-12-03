<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Request\Request;

interface NoRouteHandlerInterface
{
    /**
     * Check and process no route request
     */
    public function process(Request $request): bool;
}
