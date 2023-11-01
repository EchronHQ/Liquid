<?php

declare(strict_types=1);

namespace Liquid\Core\Router;

class Admin extends Base
{
    protected array $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    protected string|null $pathPrefix = 'admin';
    protected string $defaultPath = 'admin_test';
}
