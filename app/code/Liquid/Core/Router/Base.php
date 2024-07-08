<?php

declare(strict_types=1);

namespace Liquid\Core\Router;

use Liquid\Core\Helper\PathMatcher;
use Liquid\Core\Model\Action\AbstractAction;
use Liquid\Core\Model\Request\Request;
use Liquid\Framework\Exception\ContextException;
use Psr\Container\ContainerInterface;

class Base
{
    protected array $_requiredParams = ['moduleFrontName', 'actionPath', 'actionName'];
    protected string|null $pathPrefix = null;
    protected string $defaultPath = '';

    /** @var array{moduleName:string,actions:string[]}[] */
    private array $modules = [];
    protected array $reservedWords = [
        'abstract', ' and ', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone ', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo ', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include ', ' instanceof',
        'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', ' or ', 'print', 'private', 'protected',
        'public', 'require ', 'return ', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
        'while', ' xor ', 'void',
    ];

    public function __construct(private readonly ContainerInterface $diContainer)
    {

    }

    protected function parseRequest(Request $request): array
    {
        $output = [];
        $path = trim($request->getPathInfo(), '/');
        $params = explode('/', $path !== '' ? $path : $this->defaultPath);
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = count($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }

    public function match(Request $request): AbstractAction|null
    {
        $params = $this->parseRequest($request);
        return $this->matchAction($request, $params);
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

    protected function matchAction(Request $request, array $params): AbstractAction|null
    {

        //$moduleFrontName = $this->matchModuleFrontName($request, $params['moduleFrontName']);

        $moduleFrontName = $params['moduleFrontName'];
        //        echo \get_class($this) . ':<br/>';

        //        \var_dump($moduleFrontName);
        if (\is_null($moduleFrontName)) {
            return null;
        }
        $modules = $this->getModulesByFrontName($moduleFrontName);

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


        if (empty($modules)) {
            return null;
        }
        //        echo '<pre>' . \json_encode($modules, \JSON_PRETTY_PRINT) . '</pre>';


        $actionPath = $this->matchActionPath($request, $params['actionPath']);
        $actionName = $this->matchActionName($request, $params['actionName']);

        $fullAction = $actionPath;
        if (!empty($actionName)) {
            $fullAction .= '/' . $actionName;
        }


        $debug = '<div style="position: absolute;z-index: 999;bottom:5px;left:5px; background:#ffffff94; border:1px dotted red;padding: 10px">';
        $debug .= '    <div><b>Module:</b> ' . $moduleFrontName . '</div>';
        $debug .= '    <div><b>Action:</b> ' . $fullAction . '</div>';
        $debug .= '    <div><b>Params</b> ' . \json_encode($params['variables'] ?? []) . '</div>';

        foreach ($modules as $module) {
            $debug .= '    <div><b>Module "' . $module['moduleName'] . '" actions</b>:</div>';
            foreach ($module['actions'] as $name => $actionClass) {
                $debug .= '    <div style="padding-left:10px">- <b>' . $name . '</b>:' . $actionClass . '</div>';
            }
        }

        $debug .= '</div>';
        // echo $debug;
        foreach ($modules as $module) {

            $actionClassName = $this->getActionClassNew($module, $fullAction, $request);

            if ($actionClassName !== null) {
                // TODO: handle exceptions
                $actionInstance = $this->diContainer->get($actionClassName);

                if (isset($params['variables'])) {
                    $request->setParams($params['variables']);
                }

                //                echo '<pre>' . \json_encode($request->getParams(), \JSON_PRETTY_PRINT) . '</pre>';
                return $actionInstance;
            }

        }


        return null;
    }

    final public function getActionClassNew(array $module, string $actionPath, Request $request): string|null
    {
        $actions = $module['actions'];
        foreach ($actions as $name => $actionClass) {
            if (PathMatcher::matches($name, $actionPath)) {

                $arguments = PathMatcher::getMatchValues($name, $actionPath);
                if ($arguments !== null) {
                    foreach ($arguments as $key => $value) {
                        $key = \substr($key, 1);
                        $request->setParam($key, $value);
                    }
                }


                return $actionClass;
            }
        }
        return null;
    }

    final protected function getModulesByFrontName(string $moduleName): array
    {
        $modules = [];

        foreach ($this->modules as $module) {
            // TODO: match wildcards
            $matches = ($module['moduleName'] === $moduleName);
            if ($matches) {
                $modules[] = $module;
            }
        }
        return $modules;
    }

    final protected function matchModuleFrontName(Request $request, string $param): string|null
    {
        // get module name
        if ($param !== '') {
            $moduleFrontName = $param;
        } else {
            //            $moduleFrontName = $this->_defaultPath->getPart('module');
            //            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, '');
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
