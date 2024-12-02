<?php
declare(strict_types=1);

namespace Liquid\Framework\App;


use Liquid\Core\Helper\AccessHelper;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Repository\UrlRepository;
use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\App\Router\RouterList;
use Liquid\Framework\Controller\Result\NoAccess;
use Liquid\Framework\Controller\ResultInterface;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManager;
use Psr\Log\LoggerInterface;

class FrontController
{
    /**
     * @param LoggerInterface $logger
     * @param \Liquid\Framework\App\Config\AppConfig $config
     * @param RouterList $routerList
     * @param ObjectManager $diContainer
     * @param Resolver $resolver
     * @param State $appState
     * @param AreaList $areaList
     * @param Response $response
     * @param Profiler $profiler
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AppConfig       $config,
        private readonly RouterList      $routerList,
        private readonly ObjectManager   $diContainer,
        private readonly Resolver        $resolver,
        private readonly State           $appState,
        private readonly AreaList        $areaList,
        private readonly Response        $response,
        private readonly Profiler        $profiler,
    )
    {

    }

    public function dispatch(Request $request): ResultInterface
    {
        if (!AccessHelper::hasAccess($request)) {
            $data = [
                'ip' => $request->getIp(),
                'headers' => $request->getHeaders(),
            ];
            $this->logger->error('Unauthorized access', $data);
            return new NoAccess('<div style="color:#ffffff">No access ' . $request->getIp() . '</div>');
        }

        $this->profiler->profilerStart('frontcontroller.dispatch');

        //  $this->detectLocale($request);


        $this->config->setValue('current_path', $request->getPathInfo());

        /**
         * TODO: do we need to rewrite before checking access? And removing this method out of "handle"?
         */
        /** @var UrlRepository $urlRepository */
//        $urlRepository = $this->diContainer->get(UrlRepository::class);
//
//        $rewrite = $urlRepository->getRewrite($request->getPathInfo());
//        var_dump($rewrite);
////        echo $request->getPathInfo() . '<br/>';
//        if ($rewrite !== null) {
////            echo 'set rewrite ' . $rewrite->target . ' ' . $request->getPathInfo() . '<br/>';
//            if ($rewrite->statusCode === UrlRewriteType::INTERNAL) {
//                $request->setPathInfo($rewrite->target, $rewrite);
//            } else {
//                return new Redirect($this->resolver->getUrl($rewrite->target), $rewrite->statusCode->value);
//            }
//
//        }


        //   $area = $this->detectArea($request);

        $this->logger->debug('Incoming request', [
            'path info' => $request->getPathInfo(),
            'params' => $request->getParams(),
        ]);

        $routingCycleCounter = 0;
        $result = null;
        while (!$request->isMatched() && $routingCycleCounter++ < 100) {

            $routerIds = $this->routerList->getRouterIds();
//            var_dump($routerIds);
//
//            var_dump($request->getPathInfo() . ' ' . ($request->isMatched() ? 'Y' : 'N'));
            foreach ($routerIds as $routerId) {
                try {
                    $router = $this->routerList->getRouterInstance($routerId);
                    // echo get_class($router) . '<br/>';
                    $action = $router->match($request);
                    if ($action !== null) {
                        $result = $this->processRequest($request, $action);
                        break;
                    }

                } catch (NotFoundException $ex) {


                    $request->setPathInfo('content/noroute/index');
                    // echo $ex->getMessage() . '<br/>';
//                    $request->initForward();
//                    $request->setActionName('noroute');
                    $request->setMatched(false);
                    break;
                }
            }

        }
        $this->profiler->profilerFinish('frontcontroller.dispatch');

        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $result;


        // $routers = $this->routerList[$area === AreaCode::Frontend ? 'frontend' : 'backend'];


        // TODO: make it possible to easily ignore certain paths
//        $ignoreIfPathStartsWith = [
//            '/wp', '/bk', '/bc', '/wordpress',
//        ];
//        // /packages/barryvdh/elfinder/js/elfinder.min.js
//        $pathInfo = $request->getPathInfo();
//        if (!StringHelper::startsWith($pathInfo, $ignoreIfPathStartsWith)) {
//            $debugRoutes = [];
//            foreach ($this->routerList as $router) {
//                $debugRoutes[get_class($router)] = $router->getInfo();
//            }
//            // TODO: add referer
//            $this->logger->error('Page not found', [
//                'path info' => $request->getPathInfo(),
//                'params' => $request->getParams(),
//                'routes' => $debugRoutes,
//            ]);
//        }
//
//        /** @var NotFound $notFoundAction */
//        $notFoundAction = $this->diContainer->create(NotFound::class);
//
//        return $notFoundAction->execute();
    }

    private function processRequest(Request $request, ActionInterface $action): ResultInterface
    {
        $request->setMatched(true);
        // $this->response->setNoCacheHeaders();
        // $result = null;
        // $area = $this->areaList->getArea($this->appState->getAreaCode());

        $result = $action->execute();


//        if ($result instanceof NotFoundException) {
//            throw $result;
//        }
        //   if ($result !== null) {
        $this->logger->debug('Router matched', [
            'Request' => $request->getPathInfo(),
            'Action' => \get_class($action),
        ]);
        //  }
        return $result;
    }
}
