<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\Request;

interface RouterInterface
{
    /**
     * Match application action by request
     *
     * @param Request $request
     * @return ActionInterface|null
     */
    public function match(Request $request): ActionInterface|null;
}
