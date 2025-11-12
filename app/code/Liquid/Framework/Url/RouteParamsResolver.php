<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

use Liquid\Framework\App\Request\HttpRequest;
use Liquid\Framework\DataObject;
use Liquid\Framework\Escaper;

class RouteParamsResolver extends DataObject
{
    public function __construct(
        private readonly HttpRequest         $request,
        private readonly QueryParamsResolver $queryParamsResolver,
        private readonly Escaper             $escaper,
        array                                $data = []
    )
    {
        parent::__construct($data);
    }

    public function setRouteParams(array $data, bool $unsetOldParams = true): self
    {
        if (isset($data['_type'])) {
            $this->setData('type', $data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_forced_secure'])) {
            $this->setSecure((bool)$data['_forced_secure']);
            $this->setSecureIsForced(true);
            unset($data['_forced_secure']);
        } elseif (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        if (isset($data['_current'])) {
            if (\is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (\array_key_exists($key, $data) || !$this->request->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->request->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->request->getUserParams() as $key => $value) {
                    if (\array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->request->getQuery() as $key => $value) {
                    $this->queryParamsResolver->setQueryParam($key, $value);
                }
            }
            unset($data['_current']);
        }

        if (isset($data['_use_rewrite'])) {
            unset($data['_use_rewrite']);
        }

        foreach ($data as $key => $value) {
            if (!\is_scalar($value) || $key == 'key' || !$this->getData('escape_params')) {
                $this->setRouteParam($key, $value);
            } else {
                $this->setRouteParam(
                    $this->escaper->encodeUrlParam($key),
                    $this->escaper->encodeUrlParam((string)$value)
                );
            }
        }

        return $this;
    }

    /**
     * Set route param
     *
     * @param string $key
     * @param mixed $data
     * @return RouteParamsResolver
     */
    public function setRouteParam(string $key, mixed $data): self
    {
        $params = $this->_getData('route_params');
        if (isset($params[$key]) && $params[$key] === $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    /**
     * Retrieve route param
     *
     * @param string $key
     * @return mixed
     */
    public function getRouteParam(string $key): mixed
    {
        return $this->getData('route_params', $key);
    }

    /**
     * Retrieve route params
     */
    public function getRouteParams(): array|null
    {
        return $this->_getData('route_params');
    }

    public function setSecure(bool $isForced): void
    {
        $this->setData('secure', $isForced);
    }

    public function getSecure(): bool
    {
        return $this->getData('secure');
    }
}
