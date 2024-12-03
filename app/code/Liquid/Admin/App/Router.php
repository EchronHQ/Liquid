<?php
declare(strict_types=1);

namespace Liquid\Admin\App;

use Liquid\Framework\App\Router\BaseRouter;

class Router extends BaseRouter
{
    protected bool $requestHasAreaFrontName = true;

//    protected array $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

//    protected string|null $pathPrefix = FrontNameResolver::AREA_CODE;

    protected string $defaultPath = 'admin_test';
}
