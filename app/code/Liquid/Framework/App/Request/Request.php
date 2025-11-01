<?php

declare(strict_types=1);

namespace Liquid\Framework\App\Request;


use Liquid\Framework\StringHelper;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

class Request
{
    public const int DEFAULT_HTTP_PORT = 80;
    public const int DEFAULT_HTTPS_PORT = 443;

    public array $params = [];
    protected string $requestString = '';

    // TODO: why do we save the rewrites?
    private string|null $pathInfo = null;
    /** @var UrlRewrite[] */
    private array $rewrites = [];
    private array $paramAliases = [];
    private bool $isMatched = false;
    private string|null $distroBaseUrl = null;

    private string|null $controller = null;
    private string|null $route = null;
    private string|null $action;
//    private function getUrlSegment(int $index): ?string
//    {
//        if (isset($this->urlSegments[$index])) {
//            return $this->urlSegments[$index];
//        }
//        return null;
//    }


//    public function getControllerSegment(): ?string
//    {
//        $area = $this->getArea();
//        if ($area === Area::Frontend) {
//            return $this->getUrlSegment(0);
//        }
//        return $this->getUrlSegment(1);
//    }
//
//    public function getMethodSegment(): ?string
//    {
//        $area = $this->getArea();
//        if ($area === Area::Frontend) {
//            return $this->getUrlSegment(1);
//        }
//        return $this->getUrlSegment(2);
//    }
    private readonly \Symfony\Component\HttpFoundation\Request $request;

    public function __construct(
        private readonly StringHelper $converter,
    )
    {
        $this->request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    }

    /**
     * Retrieve the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * Set route name
     *
     * @param string $route
     * @return $this
     */
    public function setRouteName(string $route): self
    {
        $this->route = $route;
//        $module = $this->routeConfig->getRouteFrontName($route);
//        if ($module) {
//            $this->setModuleName($module);
//        }
        return $this;
    }

    /**
     * Retrieve route name
     *
     * @return string|null
     */
    public function getRouteName(): string|null
    {
        return $this->route;
    }

    public function setActionName(string $value): self
    {
        $this->action = $value;
        return $this;
    }

    public function getActionName(): string|null
    {
        return $this->action;
    }

    final public function getIp(): string
    {
        $ip = $this->request->getClientIp();
        if (empty($ip)) {
            $ip = $this->request->server->getString('REMOTE_ADDR');
        }
//        if (!empty($this->getServer('HTTP_CLIENT_IP'))) {
//            //ip from share internet
//            $ip = $this->getServer('HTTP_CLIENT_IP');
//        } elseif (!empty($this->getServer('HTTP_X_FORWARDED_FOR'))) {
//            //ip pass from proxy
//            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
//        } else {
//            $ip = $this->getServer('REMOTE_ADDR');
//        }
        return $ip;

    }

    final public function isAjax(): bool
    {
        return $this->request->isXmlHttpRequest();
    }

    /**
     * Retrieve request front name
     *
     * @return string|null
     */
    public function getFrontName(): string|null
    {
        $pathParts = \explode('/', \trim($this->getPathInfo(), '/'));
        return \reset($pathParts);
    }

    final public function getPathInfo(): string
    {
        if (empty($this->pathInfo)) {
            $this->setPathInfo();
        }
        return $this->pathInfo;
    }

    final public function setPathInfo(string|null $pathInfo = null, UrlRewrite|null $rewrite = null): self
    {
        if ($pathInfo === null) {
            $requestUri = $this->request->getRequestUri();

            // Remove the query string from REQUEST_URI
            $pos = \strpos($requestUri, '?');
            if ($pos) {
                $requestUri = \substr($requestUri, 0, $pos);
            }
            $baseUrl = $this->request->getBaseUrl();
            $pathInfo = \substr($requestUri, \strlen($baseUrl));
            if (!empty($baseUrl) && $pathInfo === '/') {
                $pathInfo = '';
            } elseif ($baseUrl === null) {
                $pathInfo = $requestUri;
            }
            // TODO: do we need to be sure that pathInfo never starts with a slash or will this be the case anyway?
            //   $pathInfo = trim($pathInfo, '/');

            $this->requestString = $pathInfo . ($pos !== false ? \substr($requestUri, $pos) : '');
        }
        $pathInfo = \trim($pathInfo, '/');

        $this->pathInfo = $pathInfo;
        if ($rewrite !== null) {
            $this->rewrites[] = $rewrite;
        }
        return $this;
    }

    /**
     * Get request string
     *
     * @return string
     */
    public function getRequestString()
    {
        return $this->requestString;
    }

    /**
     * @return string[]
     */
    final public function getPathSegments(): array
    {
        $path = $this->getPathInfo();
        $path = \ltrim($path, '/');
        return \explode('/', $path);
    }

    final public function hasParam(string $key): bool
    {
        return $this->getParam($key) !== null;
    }

    final public function getParam(string $key, string|int|null $default = null): string|int|null
    {
        $keyName = (null !== ($alias = $this->getParamAlias($key))) ? $alias : $key;
        return $this->params[$keyName] ?? $this->queryParams[$keyName] ?? $this->postParams[$keyName] ?? $default;
    }

    final public function getParams(): array
    {
        return $this->params;
    }

    final public function setParams(array $array): self
    {
        foreach ($array as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    final public function setParam(string $key, string|int|null $value): self
    {
        $keyName = (null !== ($alias = $this->getParamAlias($key))) ? $alias : $key;
        if ((null === $value) && isset($this->params[$keyName])) {
            unset($this->params[$keyName]);
        } elseif (null !== $value) {
            $this->params[$keyName] = $value;
        }
        return $this;
    }

    /**
     * @return UrlRewrite[]
     */
    final public function getRewriteInfo(): array
    {
        return $this->rewrites;
    }

    /**
     * Set flag indicating whether or not request has been matched
     *
     * @param bool $flag
     * @return self
     */
    public function setMatched(bool $flag = true): self
    {
        $this->isMatched = $flag;
        return $this;
    }

    /**
     * Determine if the request has been matched
     *
     * @return bool
     */
    public function isMatched(): bool
    {
        return $this->isMatched;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->isSecure() ? 'http' : 'https';
    }

    /**
     * Is https secure request
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->request->isSecure();
//        if ($this->immediateRequestSecure()) {
//            return true;
//        }
//        // return false;//$this->getSslOffloadHeader()
//        return $this->initialRequestSecure('X-Forwarded-Proto');
    }

    /**
     * Retrieve HTTP HOST
     *
     * @param bool $trimPort
     * @return string|null
     *
     * @todo getHttpHost should return only string (currently method return boolean value too)
     */
    public function getHttpHost(bool $trimPort = true): string|null
    {
        if ($trimPort) {
            return $this->request->getHost();
        }
        return $this->request->getHttpHost();
//        $httpHost = $this->request->getHttpHost();
//        /** Clean non UTF-8 characters */
//        $httpHost = \mb_convert_encoding($httpHost, 'UTF-8');
//        if (empty($httpHost)) {
//            return null;
//        }
//        if ($trimPort) {
//            $host = \explode(':', $httpHost);
//            return $host[0];
//        }
//        return $httpHost;
    }

    /**
     * Get website instance base url
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDistroBaseUrl(): string
    {
        if ($this->distroBaseUrl !== null) {
            return $this->distroBaseUrl;
        }
        $headerHttpHost = $this->request->getHttpHost();
        $headerHttpHost = $this->converter->cleanString($headerHttpHost);
        $headerScriptName = $this->request->getScriptName();

        if (isset($headerScriptName) && $headerHttpHost !== '') {
            if ($secure = $this->isSecure()) {
                $scheme = 'https://';
            } else {
                $scheme = 'http://';
            }

            $hostArr = \explode(':', $headerHttpHost);
            $host = $hostArr[0];
            $port = isset($hostArr[1])
            && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443) ? ':' . $hostArr[1] : '';
            $path = $this->request->getBasePath();

            return $this->distroBaseUrl = $scheme . $host . $port . \rtrim($path, '/') . '/';
        }
        return 'http://localhost/';
    }

    /**
     * Checks if the immediate request is delivered over HTTPS
     *
     * @return bool
     */
//    protected function immediateRequestSecure(): bool
//    {
//        $https = $this->request->isSecure() getServer('HTTPS');
//        $headerServerPort = $this->getServer('SERVER_PORT');
//        return (!empty($https) && $https !== 'off') || $headerServerPort == 443;
//    }

    /**
     * In case there is a proxy server, checks if the initial request to the proxy was delivered over HTTPS
     *
     * @param string $offLoaderHeader
     * @return bool
     */
//    protected function initialRequestSecure(string $offLoaderHeader): bool
//    {
//        // Transform http header to $_SERVER format ie X-Forwarded-Proto becomes $_SERVER['HTTP_X_FORWARDED_PROTO']
//        $offLoaderHeader = \str_replace('-', '_', \strtoupper($offLoaderHeader));
//        // Some webservers do not append HTTP_
//        $header = $this->getServer($offLoaderHeader);
//        // Apache appends HTTP_
//        $httpHeader = $this->getServer('HTTP_' . $offLoaderHeader);
//        return !empty($offLoaderHeader) && ($header === 'https' || $httpHeader === 'https');
//    }
    public function getServer($key)
    {
        return $this->request->server->get($key);
    }

    public function isHead(): bool
    {
        return $this->request->getMethod() === 'HEAD';
    }

    private function getParamAlias(string $key): string|null
    {
        return $this->paramAliases[$key] ?? null;
    }
}
