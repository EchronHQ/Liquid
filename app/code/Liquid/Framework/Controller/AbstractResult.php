<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\App\Response\ResponseInterface;

abstract class AbstractResult implements ResultInterface
{
    protected int|null $httpResponseCode = null;
    protected array $headers = [];

    protected int|null $statusHeaderCode = null;
    protected string $statusHeaderVersion;
    protected string $statusHeaderPhrase;

    /**
     * Render content
     *
     * @param ResponseInterface $response
     * @return self
     */
    final public function renderResult(ResponseInterface $response): self
    {
        $this->applyHttpHeaders($response);
        return $this->render($response);
    }

    public function setHttpResponseCode(int $httpCode): self
    {
        $this->httpResponseCode = $httpCode;
        return $this;
    }

    /**
     * @param int $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return $this
     */
    public function setStatusHeader(int $httpCode, int|string|null $version = null, string|null $phrase = null): self
    {
        $this->statusHeaderCode = $httpCode;
        $this->statusHeaderVersion = $version;
        $this->statusHeaderPhrase = $phrase;
        return $this;
    }

    public function setHeader(string $name, string $value, bool $replace = false): self
    {
        $this->headers[] = [
            'name' => $name,
            'value' => $value,
            'replace' => $replace,
        ];
        return $this;
    }

    protected function applyHttpHeaders(HttpResponseInterface $response): self
    {
        if ($this->httpResponseCode !== null) {
            $response->setHttpResponseCode($this->httpResponseCode);
        }
        if ($this->statusHeaderCode !== null) {
            $response->setStatusHeader(
                $this->statusHeaderCode,
                $this->statusHeaderVersion,
                $this->statusHeaderPhrase
            );
        }
        if (!empty($this->headers)) {
            foreach ($this->headers as $headerData) {
                $response->setHeader($headerData['name'], $headerData['value'], $headerData['replace']);
            }
        }
        return $this;
    }

    /**
     * @param HttpResponseInterface $response
     * @return self
     */
    abstract protected function render(HttpResponseInterface $response): self;
}
