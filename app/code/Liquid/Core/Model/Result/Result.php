<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;

abstract class Result
{
    protected int|null $httpResponseCode = null;
    protected array $headers = [];

    abstract protected function _render(Response $response): self;

    public function setHttpResponseCode(int $httpCode): self
    {
        $this->httpResponseCode = $httpCode;
        return $this;
    }

    public function setHeader(string $name, string $value, bool $replace = false): void
    {
        $this->headers[] = [
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace,
        ];
    }


    protected function applyHttpHeaders(Response $response): self
    {
        if (!empty($this->httpResponseCode)) {
            $response->setHttpResponseCode($this->httpResponseCode);
        }

        if (!empty($this->headers)) {
            foreach ($this->headers as $headerData) {
                $response->setHeader($headerData['name'], $headerData['value'], $headerData['replace']);
            }
        }
        return $this;
    }

    final public function render(Response $response): self
    {
        $this->applyHttpHeaders($response);
        return $this->_render($response);
    }
}
