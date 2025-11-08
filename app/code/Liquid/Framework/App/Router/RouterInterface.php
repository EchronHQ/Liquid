<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\HttpRequest;

interface RouterInterface
{
    /**
     * Match application action by request
     *
     * @param HttpRequest $request
     * @return ActionInterface|null
     */
    public function match(HttpRequest $request): ActionInterface|null;
}
