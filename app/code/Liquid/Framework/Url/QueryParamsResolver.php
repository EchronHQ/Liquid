<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

use Liquid\Framework\DataObject;

class QueryParamsResolver extends DataObject
{
    /**
     * Get query params part of url
     *
     * @param bool $escape "&" escape flag
     * @return string
     */
    public function getQuery(bool $escape = false): string
    {
        if (!$this->hasData('query')) {
            $query = '';
            $params = $this->getQueryParams();
            if (is_array($params)) {
                ksort($params);
                $query = http_build_query($params, '', $escape ? '&amp;' : '&');
            }
            $this->setData('query', $query);
        }
        return $this->_getData('query');
    }

    /**
     * Set URL query param(s)
     *
     * @param mixed $data
     * @return QueryParamsResolver
     */
    public function setQuery(mixed $data): self
    {
        if ($this->_getData('query') !== $data) {
            $this->unsetData('query_params');
            $this->setData('query', $data);
        }
        return $this;
    }

    /**
     * Set query param
     *
     * @param string $key
     * @param mixed $data
     * @return QueryParamsResolver
     */
    public function setQueryParam(string $key, mixed $data): self
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        $this->setData('query_params', $params);
        return $this;
    }

    /**
     * Return Query Params
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        if (!$this->hasData('query_params')) {
            $params = [];
            if ($this->_getData('query')) {
                foreach (explode('&', $this->_getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->_getData('query_params');
    }

    /**
     * Set query parameters
     *
     * @param array $data
     * @return QueryParamsResolver
     */
    public function setQueryParams(array $data): self
    {
        return $this->setData('query_params', $data);
    }

    /**
     * Add query parameters
     *
     * @param array $data
     * @return QueryParamsResolver
     */
    public function addQueryParams(array $data): self
    {
        $this->unsetData('query');

        if ($this->_getData('query_params') == $data) {
            return $this;
        }

        $params = $this->_getData('query_params');
        if (!is_array($params)) {
            $params = [];
        }
        foreach ($data as $param => $value) {
            $params[$param] = $value;
        }
        $this->setData('query_params', $params);

        return $this;
    }


    public function _resetState(): void
    {
        $this->_data = [];
    }
}
