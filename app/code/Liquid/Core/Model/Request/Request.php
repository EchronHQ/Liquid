<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Request;

use Liquid\Content\Model\Resource\UrlRewrite;

class Request extends \Laminas\Http\PhpEnvironment\Request
{
    public array $params = [];
    private string|null $pathInfo = null;
    /** @var UrlRewrite[] */
    private array $rewrites = [];

    final public function getIp(): string
    {

        if (!empty($this->getServer('HTTP_CLIENT_IP'))) {
            //ip from share internet
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } elseif (!empty($this->getServer('HTTP_X_FORWARDED_FOR'))) {
            //ip pass from proxy
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }
        return $ip;

    }


    final public function isAjax(): bool
    {
        return $this->isXmlHttpRequest();
    }


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
            $requestUri = $this->getRequestUri();

            // Remove the query string from REQUEST_URI
            $pos = strpos($requestUri, '?');
            if ($pos) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            $baseUrl = $this->getBaseUrl();
            $pathInfo = substr($requestUri, strlen($baseUrl));
            if (!empty($baseUrl) && $pathInfo === '/') {
                $pathInfo = '';
            } elseif ($baseUrl === null) {
                $pathInfo = $requestUri;
            }
            //            $this->requestString = $pathInfo . ($pos !== false ? substr($requestUri, $pos) : '');
        }
        $this->pathInfo = $pathInfo;
        if ($rewrite !== null) {
            $this->rewrites[] = $rewrite;
        }
        return $this;
    }

    final public function setParams(array $array): self
    {
        foreach ($array as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    private array $paramAliases = [];

    private function getParamAlias(string $key): string|null
    {
        if (isset($this->paramAliases[$key])) {
            return $this->paramAliases[$key];
        }
        return null;
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

    final public function getParam(string $key, string|int|null $default = null): string|int|null
    {
        $keyName = (null !== ($alias = $this->getParamAlias($key))) ? $alias : $key;
        return $this->params[$keyName] ?? $this->queryParams[$keyName] ?? $this->postParams[$keyName] ?? $default;
    }

    final public function hasParam(string $key): bool
    {
        return $this->getParam($key) !== null;
    }

    final public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return UrlRewrite[]
     */
    final public function getRewriteInfo(): array
    {
        return $this->rewrites;
    }

}
