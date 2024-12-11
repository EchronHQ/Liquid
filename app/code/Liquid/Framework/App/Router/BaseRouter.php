<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Core\Helper\PathMatcher;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Route\Route;
use Liquid\Framework\App\Route\RouteConfig;
use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;


class BaseRouter implements RouterInterface
{
    protected bool $requestHasAreaFrontName = false;

//    protected array $_requiredParams = ['moduleFrontName', 'actionPath', 'actionName'];
//    protected string|null $pathPrefix = null;
    protected string $defaultPath = '';
    protected array $reservedWords = [
        'abstract', ' and ', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone ', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo ', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include ', ' instanceof',
        'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', ' or ', 'print', 'private', 'protected',
        'public', 'require ', 'return ', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
        'while', ' xor ', 'void',
    ];
    /** @var array{moduleName:string,actions:string[]}[] */
    private array $modules = [];

    public function __construct(
        private readonly ObjectManagerInterface $diContainer,
        private readonly RouteConfig            $routeConfig

    )
    {

    }

    public function match(Request $request): ActionInterface|null
    {
        $params = $this->parseRequest($request);
        return $this->matchAction($request, $params);
    }

    final public function getActionClassNew(Route $route, string $actionPath, Request $request): string|null
    {
        // echo '<div style="display:grid; grid-template-columns: 400px 400px auto; padding:16px 5px"><div>' . $route->path . '</div><div>' . $actionPath . '</div><div>' . (PathMatcher::matches($route->path, $actionPath) ? 'Y' : 'N') . '</div></div>';
        if (PathMatcher::matches($route->path, $actionPath)) {
            $arguments = PathMatcher::getMatchValues($route->path, $actionPath);
            if ($arguments !== null) {
                foreach ($arguments as $key => $value) {
                    $key = \substr($key, 1);
                    $request->setParam($key, $value);
                }
            }


            return $route->class;
        }
        // }
        return null;
    }

    public function registerModule(string $moduleName, array $actions): void
    {
        $formattedActions = [];
        foreach ($actions as $actionName => $controller) {
            if (\is_numeric($actionName)) {
                throw new ContextException('Numeric action names are no longer supported', ['module' => $moduleName, 'action' => $actionName]);
            }
            if (!is_a($controller, AbstractAction::class, true)) {
                throw new ContextException('Controller must extend abstract action', ['module' => $moduleName, 'action' => $actionName]);
            }

            $formattedActions[$actionName] = $controller;
        }

        $this->modules[] = [
            'moduleName' => $moduleName,
            'actions' => $formattedActions,
        ];
    }

    final public function getInfo(): array
    {
        $modules = [];

        foreach ($this->modules as $module) {

            $modules[] = $module;

        }
        return $modules;

    }

//    final protected function getModulesByFrontName(string $moduleName): array
//    {
//        $modules = [];
//
//        foreach ($this->modules as $module) {
//            // TODO: match wildcards
//            $matches = ($module['moduleName'] === $moduleName);
//            if ($matches) {
//                $modules[] = $module;
//            }
//        }
//        return $modules;
//    }
    /**
     * @param Request $request
     * @return array{areaFrontName: string, actionPath: string}
     */
    protected function parseRequest(Request $request): array
    {
        $output = [];
        $path = trim($request->getPathInfo(), '/');
        $params = explode('/', $path !== '' ? $path : $this->defaultPath);

        // The first param is the area front name (for example admin code)
        $output['areaFrontName'] = $this->requestHasAreaFrontName ? array_shift($params) : null;


        $output['actionPath'] = implode('/', $params);

//        foreach ($this->_requiredParams as $paramName) {
//            $output[$paramName] = array_shift($params);
//        }

//
//        for ($i = 0, $l = count($params); $i < $l; $i += 2) {
//            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
//        }
        return $output;
    }

    /**
     * @param Request $request
     * @param array{areaFrontName: string, actionPath: string} $params
     * @return ActionInterface|null
     * @throws \ReflectionException
     */
    protected function matchAction(Request $request, array $params): ActionInterface|null
    {
//        $moduleFrontName = $this->matchModuleFrontName($request, $params['moduleFrontName']);
//        if (\is_null($moduleFrontName)) {
//            return null;
//        }
        //  var_dump($moduleFrontName);
        $actions = $this->routeConfig->getActions('thisisnolongerused');

//        if (false) {
//            /**
//             * Debug
//             */
//            echo '<div>' . 'Request Path: ' . $request->getPathInfo() . '</div>';
//            echo '<div>Frontname: ' . $params["areaFrontName"] . '</div>';
//            echo '<div>Action: ' . $params["actionPath"] . '</div>';
//            foreach ($actions as $action) {
//                echo '<div>' . implode('|', $action->methods) . ' ' . $action->path . ' (' . $action->class . ')</div>';
//            }
//        }
        if (empty($actions)) {
            return null;
        }

        //  $modules = $this->getModulesByFrontName($moduleFrontName);

        //        \var_dump($modules);
        ////        \var_dump($moduleFrontName);
        //        \var_dump($request->getPathInfo());
        //        \var_dump($modules);
        ////        \var_dump($this->modules);
        //
        //        echo '<pre>' . $moduleFrontName . '</pre>';
        //        echo '<pre>' . \json_encode($this->modules, \JSON_PRETTY_PRINT) . '</pre>';

        //        $rewrites = $request->getRewriteInfo();
        //        foreach ($rewrites as $rewrite) {
        //            echo '<pre>' . $rewrite->request . ' => ' . $rewrite->target . ' ' . $rewrite->statusCode . '</pre>';
        //        }


//        if (empty($modules)) {
//            return null;
//        }
        //        echo '<pre>' . \json_encode($modules, \JSON_PRETTY_PRINT) . '</pre>';


//        $actionPath = $this->matchActionPath($request, $params['actionPath']);
//        $actionName = $this->matchActionName($request, $params['actionName']);
//
//        $fullAction = $moduleFrontName;
//        if (!empty($actionPath)) {
//            $fullAction .= '/' . $actionPath;
//        }
//
//        if (!empty($actionName)) {
//            $fullAction .= '/' . $actionName;
//        }


//        $debug = '<div style="position: absolute;z-index: 999;bottom:5px;left:5px; background:#ffffff94; border:1px dotted red;padding: 10px">';
//        $debug .= '    <div><b>Module:</b> ' . $moduleFrontName . '</div>';
//        $debug .= '    <div><b>Action:</b> ' . $fullAction . '</div>';
//        $debug .= '    <div><b>Params</b> ' . \json_encode($params['variables'] ?? []) . '</div>';
//
//        /** @var Route $action */
//        foreach ($actions as $action) {
//            // $debug .= '    <div><b>Module "' . $action->getPath() . '" actions</b>:</div>';
//            // foreach ($action['actions'] as $name => $actionClass) {
//            $debug .= '    <div style="padding-left:10px">- <b>' . $action->path . '</b>:' . $action->class . '</div>';
//            // }
//        }
//
//        $debug .= '</div>';
        // echo $debug;
        foreach ($actions as $action) {

            // echo $module->path . '<br/>';
            $actionClassName = $this->getActionClassNew($action, $params['actionPath'], $request);

            if ($actionClassName !== null) {
                // TODO: handle exceptions
                $actionInstance = $this->diContainer->get($actionClassName);

//                if (!$actionInstance instanceof AbstractAction) {
//                    throw new \RuntimeException('Action should be "' . AbstractAction::class . '", "' . get_class($actionInstance) . '" given');
//                }

//                if (isset($params['variables'])) {
//                    $request->setParams($params['variables']);
//                }

                //                echo '<pre>' . \json_encode($request->getParams(), \JSON_PRETTY_PRINT) . '</pre>';
                return $actionInstance;
            }

        }


        return null;
    }

    /**
     * TODO: give the first section of the url a better name than "module"
     * @param Request $request
     * @param string $param
     * @return string|null
     */
    final protected function matchModuleFrontName(Request $request, string $param): string|null
    {
        // get module name
        if ($param !== '') {
            $moduleFrontName = $param;
        } else {
            //            $moduleFrontName = $this->_defaultPath->getPart('module');
            //            $request->setAlias(\Liquid\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, '');
            //            if (!$moduleFrontName) {
            //                return null;
            //            }

            $moduleFrontName = null;
        }

        return $moduleFrontName;
    }

    final protected function matchActionPath(Request $request, string|null $param): string
    {
        if ($param === null || $param === '') {
            return '';
        }
        return $param;
    }

    protected function matchActionName(Request $request, string|null $param): string
    {
        if ($param === null || $param === '') {
            return '';
        }
        return $param;
    }
}
