<?php
declare(strict_types=1);

namespace Liquid\Framework;

use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Scope\ScopeId;
use Liquid\Framework\App\Scope\ScopeInterface;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\Url\QueryParamsResolver;
use Liquid\Framework\Url\RouteParamsResolver;
use Liquid\Framework\Url\ScopeResolverInterface;
use Liquid\Framework\Url\UrlType;
use Psr\Log\LoggerInterface;

class Url extends DataObject
{
    public const UrlType DEFAULT_URL_TYPE = UrlType::LINK;
    /** @var string[] */
    private array $cacheUrl = [];

    public function __construct(
        private readonly ScopeConfig            $segmentConfig,
        private readonly ScopeResolverInterface $scopeResolver,
        private readonly RouteParamsResolver    $routeParamsResolver,
        private readonly QueryParamsResolver    $queryParamsResolver,
        private readonly Request                $request,
        private readonly SerializerInterface    $serializer,
        private readonly Escaper                $escaper,
        private readonly LoggerInterface        $logger,
        array                                   $data = [],
    )
    {
        parent::__construct($data);
    }

    /**
     * TODO: this needs better + remove this from url class (move it to segment class maybe?)
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return string
     * @throws \Exception
     */
    public function getUrl(string|null $routePath = null, array|null $routeParams = null): string
    {

        if ($routePath !== null && $this->isUrl($routePath)) {
            return $routePath;
        }
        $paramsIsArray = \is_array($routeParams);
        $allowCache = true;
        //  var_dump($path);
        if ($paramsIsArray) {
            \array_walk_recursive(
                $routeParams,
                static function ($item) use (&$allowCache) {
                    if (\is_object($item)) {
                        $allowCache = false;
                    }
                }
            );
        }
        if (!$allowCache) {
//            return $this->getUrlModifier()->execute(
//                $this->createUrl($routePath, $routeParams)
//            );
            return $this->createUrl($routePath, $routeParams);
        }
//        $this->segmentConfig->get()

        //   $this->segmentResolver->getCurrentSegmentId()
        //     $segment = $this->segmentManager->getSegment($segmentId);


//        $defaultLocale = 'en-uk';
//        if (($locale !== null && $locale->code === $defaultLocale) || !$this->segmentConfig->hasLocales()) {
//            $locale = null;
//        }
        $cachedParams = $routeParams;
        if ($paramsIsArray) {
            \ksort($cachedParams);
        }

        $cacheKey = \sha1($routePath . $this->serializer->serialize($cachedParams));
        if (!isset($this->cacheUrl[$cacheKey])) {
//            $this->cacheUrl[$cacheKey] = $this->getUrlModifier()->execute(
//                $this->createUrl($routePath, $routeParams)
//            );
            $this->cacheUrl[$cacheKey] = $this->createUrl($routePath, $routeParams);
        }

//        if ($locale === null) {
        // return $this->segmentConfig->getValue('web/unsecure/base_url') . $path;
//        }
        //  return $segment->getBaseUrl() . '/' . $path;
        return $this->cacheUrl[$cacheKey];

    }

    /**
     * Retrieve Base URL
     *
     * @param array $params
     * @return string
     */
    public function getBaseUrl(array $params = []): string
    {
        /**
         *  Original Scope
         */
        $originalSegment = $this->getSegment();

        if (isset($params['_segment'])) {
            $this->setScope($params['_segment']);
        }
        if (isset($params['_type'])) {
            $this->routeParamsResolver->setData('type', $params['_type']);
        }
        if (isset($params['_secure'])) {
            $this->routeParamsResolver->setSecure($params['_secure']);
        }

        $result = $this->getSegment()->getBaseUrl($this->_getType(), $this->_isSecure());

        // setting back the original scope
        $this->setScope($originalSegment->getId());
        $this->routeParamsResolver->setData('type', self::DEFAULT_URL_TYPE);

        return $result;
    }

    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        $httpHostWithPort = $this->request->getHttpHost(false);
        $httpHostWithPort = \explode(':', $httpHostWithPort);
        $httpHost = $httpHostWithPort[0] ?? '';
        $port = '';
        if (isset($httpHostWithPort[1])) {
            $defaultPorts = [
                Request::DEFAULT_HTTP_PORT,
                Request::DEFAULT_HTTPS_PORT,
            ];
            /** Only add custom port to url when it's not a default one */
            if (!\in_array($httpHostWithPort[1], $defaultPorts, true)) {
                $port = ':' . $httpHostWithPort[1];
            }
        }
        return $this->request->getScheme() . '://' . $httpHost . $port . $this->request->getRequestUri();
    }

    /**
     * Set scope entity
     *
     * @param ScopeId|null $scopeId
     * @return Url
     */
    public function setScope(ScopeId|null $scopeId): Url
    {
        $scope = $this->scopeResolver->getScope($scopeId);
        $this->setData('scope', $scope);
        // $this->getRouteParamsResolver()->setScope($scope);

        return $this;
    }

    /**
     * Retrieve route URL
     *
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return string
     */
    public function getRouteUrl(string|null $routePath = null, array|null $routeParams = null): string
    {
        if (\filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $this->routeParamsResolver->unsetData('route_params');

        if (isset($routeParams['_direct'])) {
            if (\is_array($routeParams)) {
                $this->_setRouteParams($routeParams, false);
            }
            return $this->getBaseUrl() . $routeParams['_direct'];
        }

        if ($routePath !== null) {
            $this->_setRoutePath($routePath);
        }
        if (\is_array($routeParams)) {
            $this->_setRouteParams($routeParams, false);
        }
        if ($routeParams === null) {
            $routeParams = [];
        }
        return $this->getBaseUrl($routeParams) . $this->_getRoutePath($routeParams);
    }

    /**
     * Set Route Parameters
     *
     * @param string $data
     * @return  Url
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _setRoutePath(string $data): self
    {
        if ($this->_getData('route_path') === $data) {
            return $this;
        }

        $this->unsetData('route_path');
        $route = '';
        $routePieces = [];
        if (!empty($data)) {
            $routePieces = \explode('/', $data);
            $route = \array_shift($routePieces);
            if ('*' === $route) {
                $route = $this->request->getActionName();
            }
        }
//        echo $data . PHP_EOL;
//        var_dump($routePieces);
        $this->_setRouteName($route);

//        $controller = '';
//        if (!empty($routePieces)) {
//            $controller = array_shift($routePieces);
//            if ('*' === $controller) {
//                $controller = $this->request->getControllerName();
//            }
//        }
//        $this->_setControllerName($controller);

        $action = '';
        if (!empty($routePieces)) {
            $action = \array_shift($routePieces);
            if ('*' === $action) {
                $action = $this->request->getActionName();
            }
        }
        $this->_setActionName($action);

        if (!empty($routePieces)) {
            while (!empty($routePieces)) {
                $key = \array_shift($routePieces);
                if (!empty($routePieces)) {
                    $value = \array_shift($routePieces);
                    $this->routeParamsResolver->setRouteParam($key, $value);
                }
            }
        }

        return $this;
    }

    protected function _setActionName(string $data): self
    {
        if ($this->_getData('action_name') === $data) {
            return $this;
        }
        $this->unsetData('route_path');
        $this->setData('action_name', $data);
        $this->queryParamsResolver->unsetData('secure');
        return $this;
    }

    /**
     * Set route name
     *
     * @param string $data
     * @return Url
     */
    protected function _setRouteName(string $data): self
    {
        if ($this->_getData('route_name') == $data) {
            return $this;
        }
        $this->unsetData('route_front_name')
            ->unsetData('route_path')
            ->unsetData('controller_name')
            ->unsetData('action_name');
        $this->queryParamsResolver->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    /**
     * Retrieve route name
     *
     * @param mixed $default
     * @return string|null
     */
    protected function _getRouteName(string|null $default = null): string|null
    {
        return $this->_getData('route_name') ? $this->_getData('route_name') : $default;
    }

    /**
     * Retrieve route path
     *
     * @param array $routeParams
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getRoutePath(array $routeParams = []): string
    {
        if (!$this->hasData('route_path')) {
//            $routePath = $this->request->getAlias(self::REWRITE_REQUEST_PATH_ALIAS);
//            if (!empty($routeParams['_use_rewrite']) && $routePath !== null) {
//                $this->setData('route_path', $routePath);
//                return $routePath;
//            }
            $routePath = $this->_getActionPath();

            $routeParams = $this->_getRouteParams();

            if ($routeParams) {
                foreach ($routeParams as $key => $value) {
                    if ($value === null || false === $value || '' === $value || !\is_scalar($value)) {
                        continue;
                    }
                    $routePath .= $key . '/' . $value . '/';
                }
            }
            $this->setData('route_path', $routePath);
        }
        return $this->_getData('route_path');
    }

    /**
     * Retrieve action path
     *
     * @return string
     */
    protected function _getActionPath(): string
    {
        if (!$this->_getRouteName()) {
            return '';
        }

        $hasParams = (bool)$this->_getRouteParams();
        $path = $this->_getActionName() . '/';
//        if ($this->_getControllerName()) {
//            $path .= $this->_getControllerName() . '/';
//        } elseif ($hasParams) {
//            $path .= self::DEFAULT_CONTROLLER_NAME . '/';
//        }
//        if ($this->_getActionName()) {
//            $path .= $this->_getActionName() . '/';
//        } elseif ($hasParams) {
//            $path .= self::DEFAULT_ACTION_NAME . '/';
//        }

        return $path;
    }

    protected function _getActionName(string|null $default = null): string|null
    {
        return $this->_getData('action_name') ? $this->_getData('action_name') : $default;
    }

    /**
     * Retrieve controller name
     */
    protected function _getControllerName(string|null $default = null): string|null
    {
        return $this->_getData('controller_name') ? $this->_getData('controller_name') : $default;
    }

    /**
     * Retrieve route front name
     *
     * @return string
     */
//    protected function _getRouteFrontName()
//    {
//        if (!$this->hasData('route_front_name')) {
//            $frontName = $this->_routeConfig->getRouteFrontName(
//                $this->_getRouteName(),
//                $this->_scopeResolver->getAreaCode()
//            );
//            $this->setData('route_front_name', $frontName);
//        }
//        return $this->_getData('route_front_name');
//    }

    /**
     * Retrieve route params
     *
     * @return array
     */
    protected function _getRouteParams(): array
    {
        $params = $this->routeParamsResolver->getRouteParams();
        if (is_null($params)) {
            return [];
        }
        return $params;
    }

    /**
     * Set route params
     *
     * @param array $data
     * @param bool $unsetOldParams
     * @return Url
     */
    protected function _setRouteParams(array $data, bool $unsetOldParams = true): Url
    {
        $this->routeParamsResolver->setRouteParams($data, $unsetOldParams);
        return $this;
    }

    /**
     * Retrieve URL type
     */
    protected function _getType(): UrlType
    {
        if (!$this->routeParamsResolver->hasData('type')) {
            $this->routeParamsResolver->setData('type', self::DEFAULT_URL_TYPE);
        }
        return $this->routeParamsResolver->getData('type');
    }

    /**
     * Retrieve is secure mode URL
     *
     * @return bool
     */
    protected function _isSecure(): bool
    {
        if ($this->request->isSecure()) {
            if ($this->routeParamsResolver->hasData('secure')) {
                return (bool)$this->routeParamsResolver->getData('secure');
            }
            return true;
        }

        if ($this->routeParamsResolver->hasData('secure_is_forced')) {
            return (bool)$this->routeParamsResolver->getData('secure');
        }

        if (!$this->getSegment()->isUrlSecure()) {
            return false;
        }

        if (!$this->routeParamsResolver->hasData('secure')) {
            if ($this->_getType() === UrlType::LINK) {
                $pathSecure = true;// $this->_urlSecurityInfo->isSecure('/' . $this->_getActionPath());
                $this->routeParamsResolver->setData('secure', $pathSecure);
            } elseif ($this->_getType() === UrlType::STATIC) {
                $isRequestSecure = $this->request->isSecure();
                $this->routeParamsResolver->setData('secure', $isRequestSecure);
            } else {
                $this->routeParamsResolver->setData('secure', true);
            }
        }

        return $this->routeParamsResolver->getData('secure');
    }

    /**
     * Get current scope for the url instance
     *
     * @return ScopeInterface
     */
    protected function getSegment(): ScopeInterface
    {
        if (!$this->hasData('scope')) {
            $this->setScope(null);
        }
        return $this->_getData('scope');
    }

    /**
     * Build url by requested path and parameters
     *
     * @param string|null $routePath
     * @param array|null $routeParams
     * params:
     * _escape ?
     * _query: query params
     * _nosid: ?
     *
     * @return  string
     */
    private function createUrl(string|null $routePath = null, array|null $routeParams = null): string
    {
        $escapeQuery = false;
        $escapeParams = true;

        $this->routeParamsResolver->unsetData('secure');
        $fragment = null;

        if (isset($routeParams['_escape'])) {
            $escapeQuery = $routeParams['_escape'];
            unset($routeParams['_escape']);
        }

        $query = null;
        if (isset($routeParams['_query'])) {
            $this->queryParamsResolver->setQueryParams([]);
            $query = $routeParams['_query'];
            unset($routeParams['_query']);
        }

        unset($routeParams['_nosid']);
        $url = $this->getRouteUrl($routePath, $routeParams);

        /**
         * Apply query params, need call after getRouteUrl for rewrite _current values
         */
        if ($query !== null) {
            if (\is_string($query)) {
                $this->queryParamsResolver->setQuery($query);
            } elseif (\is_array($query)) {
                $this->queryParamsResolver->addQueryParams($query);
            }
            if ($query === false) {
                $this->queryParamsResolver->addQueryParams([]);
            }
        }

        $query = $this->queryParamsResolver->getQuery($escapeQuery);
        if ($query) {
            $mark = !str_contains($url, '?') ? '?' : ($escapeQuery ? '&amp;' : '&');
            $url .= $mark . $query;
            $this->queryParamsResolver->unsetData('query');
            $this->queryParamsResolver->unsetData('query_params');
        }

        if ($fragment !== null) {
            $url .= '#' . $this->escaper->escapeUrl($fragment);
        }
        $this->routeParamsResolver->unsetData('secure');
        $this->routeParamsResolver->unsetData('escape_params');

        return $url;
    }

    private function isUrl(string $url): bool
    {
        return \filter_var($url, FILTER_VALIDATE_URL);
    }
}
