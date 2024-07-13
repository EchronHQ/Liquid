<?php

declare(strict_types=1);

namespace Liquid\Core;

use DI\Container;
use Liquid\Admin\Controller\Admin\Editor\View as AdminEditorView;
use Liquid\Content\Controller\Page\NotFound;
use Liquid\Content\Helper\StringHelper;
use Liquid\Content\Model\Resource\UrlRewrite;
use Liquid\Content\Model\Resource\UrlRewriteType;
use Liquid\Content\Repository\LocaleRepository;
use Liquid\Core\Helper\AccessHelper;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\Area;
use Liquid\Core\Model\Request\Request;
use Liquid\Core\Model\Request\Response;
use Liquid\Core\Model\Result\NoAccess;
use Liquid\Core\Model\Result\Redirect;
use Liquid\Core\Model\Result\Result;
use Liquid\Core\Repository\UrlRepository;
use Liquid\Core\Repository\ViewableEntityRepository;
use Liquid\Core\Router\Admin;
use Liquid\Core\Router\Base;
use Liquid\Framework\Module\ModuleHelper;
use Psr\Log\LoggerInterface;


class Router
{
    public const PAGE_NOT_FOUND_IDENTIFIER = 'not-found';


    /** @var Base[] */
    private array $routes = [];

    /** @var array<string,string> */
    private array $pageRoutes = [];

    private bool $languageDetectionHasRan = false;

    /**  @var ViewableEntityRepository[] */
    private array $viewableEntityRepositories = [];

    public function __construct(
        private readonly Resolver         $resolver,
        private readonly AppConfig        $config,
        private readonly Container        $diContainer,
        private readonly LocaleRepository $localeRepository,
        private readonly Profiler         $profiler,
        private readonly ModuleHelper     $moduleHelper,
        private readonly LoggerInterface  $logger
    )
    {

        $frontendRoute = new Base($this->diContainer);


        $modules = $this->moduleHelper->getModules();

        $viewableEntityRepositories = [];
        foreach ($modules as $module) {

            $routes = $module->routes;
            $moduleViewableEntityRepositories = $module->viewableEntityRepositories;


            foreach ($routes as $route => $paths) {
                $frontendRoute->registerModule($route, $paths);
            }


            foreach ($moduleViewableEntityRepositories as $moduleViewableEntityRepository) {
                if (in_array($moduleViewableEntityRepository, $viewableEntityRepositories, true)) {
                    $this->logger->error('Viewable entity repository `' . $moduleViewableEntityRepository . '` is already loaded');
                } else {
                    $viewableEntityRepositories[] = $moduleViewableEntityRepository;
                }
            }
        }
        $this->viewableEntityRepositories = $viewableEntityRepositories;
        /**
         * Deprecated, redirect "connector" to "platforms" in url
         */
        //        $frontendRoute->registerModule('connector', [
        //            ConnectorView::class,
        //            ConnectorCategoryView::class,
        //            ConnectorTypeView::class,
        //            ConnectorOverviewView::class
        //        ]);
//        $frontendRoute->registerModule('platforms', [
//            ':platformId' => ConnectorView::class,
//            'category/:categoryId' => ConnectorCategoryView::class,
//            'type/:typeId' => ConnectorTypeView::class,
//            '' => ConnectorOverviewView::class,
//        ]);
//        $frontendRoute->registerModule('connect', [
//            ':platformIdentifiers' => ConnectView::class,
//        ]);
//        $frontendRoute->registerModule('pagenotfound', [
//            '' => PageNotFoundController::class,
//        ]);
        $this->routes['frontend'] = [$frontendRoute];

        /**
         * Admin routes
         */
        $adminRoute = new Admin($this->diContainer);
        $adminRoute->registerModule('admin', [
            'editor' => AdminEditorView::class,
        ]);
        $adminRoute->registerModule('layout', [
            '' => \Liquid\Content\Controller\Admin\Content\Layout::class,
        ]);
        $this->routes['backend'] = [$adminRoute];

    }


    public function initialize(): void
    {
        $request = $this->diContainer->get(Request::class);
        $this->detectLocale($request);


        $this->initializeRoutes();
        $this->initializeUrlRewrites();


    }

    private function initializeRoutes(): void
    {

    }


    private function initializeUrlRewrites(): void
    {


        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->diContainer->get(UrlRepository::class);

        foreach ($this->viewableEntityRepositories as $viewableEntityRepository) {
            $repository = $this->diContainer->get($viewableEntityRepository);
            if ($repository instanceof ViewableEntityRepository) {

                $viewableEntities = $repository->getEntities();
                foreach ($viewableEntities as $viewableEntity) {


                    if (array_key_exists($viewableEntity->id, $this->pageRoutes)) {
                        $this->logger->error('There is already a viewable entity with id `' . $viewableEntity->id . '`');
                    } else {
                        // TODO: the page routes should be loaded elsewhere, they are not used in this class
                        $this->pageRoutes[$viewableEntity->id] = $viewableEntity->getUrlPath();

                        $urlRewrites = $viewableEntity->getUrlRewrites();
                        if (count($urlRewrites) > 0) {
                            foreach ($urlRewrites as $urlRewrite) {
                                $rewrite = new UrlRewrite('/' . $viewableEntity->getUrlPath(), $urlRewrite, UrlRewriteType::INTERNAL);
                                $urlRepository->addRewrite($rewrite);
                            }
                        }
                    }
                }
            } else {
                $this->logger->error('Viewable repository must be instance of ViewableEntityRepository');
            }

        }


    }

    /**
     * @return array<string,string>
     */
    public function getPageRoutes(): array
    {
        return $this->pageRoutes;
    }


    private function detectLocale(Request $request): void
    {
        if ($this->languageDetectionHasRan) {
            $this->logger->error('Language detection by request already ran');
            return;
        }
        if (str_starts_with($request->getPathInfo(), '//')) {
            // Redirect https://attlaz.com//partners to https://attlaz.com/partners
            $redirect = str_replace('://', '+****+', $request->getUriString());
            $redirect = str_replace('//', '/', $redirect);
            $redirect = str_replace('+****+', '://', $redirect);
            header('Location: ' . $redirect, true, 301);

            exit();
        }

        $pathParts = $request->getPathSegments();

        $localeCode = reset($pathParts);


        if ($localeCode === 'en-us') {
            // TODO: when languages are disabled, redirect all to the default/empty language
            $localeCode = 'en-uk';

            $redirect = str_replace('/en-us', '', $request->getUriString());

            header('Location: ' . $redirect, true, 301);

            exit();
        }
        $locale = $this->localeRepository->getByCode($localeCode);
        if ($locale === null) {
            // TODO: detect based on browser, not just load the uk one

            //            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            //
            //            \var_dump($lang);
            //            die('--');
            $locale = $this->localeRepository->getDefault();
            $this->config->setLocale($locale, false);
        } else {
            // Remove language code from request
            // TODO: this is a side effect, we need to do this differently
            array_shift($pathParts);
            $request->setPathInfo('/' . \implode('/', $pathParts));
            $this->config->setLocale($locale, true);
        }

        $this->languageDetectionHasRan = true;
    }

    private function detectArea(Request $request): Area
    {
        $pathParts = explode('/', trim($request->getPathInfo(), '/'));
        $frontName = reset($pathParts);
        if ($frontName === $this->config->getValue('admin.url_token')) {
            return Area::Backend;
        }
        return Area::Frontend;
    }

    private function handle(Request $request, Response $response): Result
    {
        if (!AccessHelper::hasAccess($request)) {
            $data = [
                'ip' => $request->getIp(),
                'headers' => $request->getHeaders(),
            ];
            $this->logger->error('Unauthorized access', $data);
            return new NoAccess('<div style="color:#ffffff">No access ' . $request->getIp() . '</div>');
        }

        //  $this->detectLocale($request);


        $this->config->setValue('current_path', $request->getPathInfo());

        /**
         * TODO: do we need to rewrite before checking access? And removing this method out of "handle"?
         */
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->diContainer->get(UrlRepository::class);

        $rewrite = $urlRepository->getRewrite($request->getPathInfo());

//        echo $request->getPathInfo() . '<br/>';
        if ($rewrite !== null) {
//            echo 'set rewrite ' . $rewrite->target . ' ' . $request->getPathInfo() . '<br/>';
            if ($rewrite->statusCode === UrlRewriteType::INTERNAL) {
                $request->setPathInfo($rewrite->target, $rewrite);
            } else {
                return new Redirect($this->resolver->getUrl($rewrite->target), $rewrite->statusCode->value);
            }

        }


        $area = $this->detectArea($request);

        $this->logger->debug('Incoming request', [
            'path info' => $request->getPathInfo(),
            'params' => $request->getParams(),
        ]);
        //        \var_dump($request->getPathInfo());
        //        \var_dump($request->getParams());

        /** @var Base[] $routers */
        $routers = $this->routes[$area === Area::Frontend ? 'frontend' : 'backend'];


        foreach ($routers as $router) {
            $action = $router->match($request);
            if ($action !== null) {
                $result = $action->execute();
                if ($result !== null) {
                    $this->logger->debug('Router matched', ['Action' => \get_class($action)]);
                    return $result;
                }
            }

        }

        // TODO: make it possible to easily ignore certain paths
        $ignoreIfPathStartsWith = [
            '/wp', '/bk', '/bc', '/wordpress',
        ];
        // /packages/barryvdh/elfinder/js/elfinder.min.js
        $pathInfo = $request->getPathInfo();
        if (!StringHelper::startsWith($pathInfo, $ignoreIfPathStartsWith)) {
            $debugRoutes = [];
            foreach ($routers as $router) {
                $debugRoutes[get_class($router)] = $router->getInfo();
            }
            // TODO: add referer
            $this->logger->error('Page not found', [
                'path info' => $request->getPathInfo(),
                'params' => $request->getParams(),
                'routes' => $debugRoutes,
            ]);
        }

        /** @var NotFound $notFoundAction */
        $notFoundAction = $this->diContainer->make(NotFound::class);

        return $notFoundAction->execute();
    }

    public function execute(): Response
    {
        $this->profiler->profilerStart('Router:execute');

        $request = $this->diContainer->get(Request::class);
        $response = $this->diContainer->get(Response::class);


        $this->profiler->profilerStart('Router:handle');
        $result = $this->handle($request, $response);
        $this->profiler->profilerFinish('Router:handle');

        $this->profiler->profilerStart('Router:render');
        $result->render($response);
        $this->profiler->profilerFinish('Router:render');

        $this->profiler->profilerFinish('Router:execute');
        return $response;

    }

//    private function runController(string $actionClass): AbstractAction
//    {
//        return $this->diContainer->get($actionClass);
//    }

}
