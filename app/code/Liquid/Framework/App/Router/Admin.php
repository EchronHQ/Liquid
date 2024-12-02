<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

class Admin extends BaseRouter implements RouterInterface
{
    protected array $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    protected string|null $pathPrefix = 'admin';
    protected string $defaultPath = 'admin_test';
}
