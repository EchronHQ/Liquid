<?php

declare(strict_types=1);

namespace Liquid\Framework\App\Response;

use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Headers;
use Laminas\Http\Response as HttpResponse;
use Liquid\Core\Model\Request\ResponseType;

class Response extends \Laminas\Http\PhpEnvironment\Response implements HttpResponseInterface
{
    public ResponseType $type;


    public function __construct()
    {
        $this->setHeadersSentHandler(function ($response) {
            throw new \RuntimeException('Cannot send headers, headers already sent');
        });
        // parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getHttpResponseCode(): int
    {
        return $this->statusCode;

    }

    /**
     * Remove all headers
     *
     * @return $this
     */
    public function clearHeaders(): self
    {
        $headers = $this->getHeaders();
        if ($headers !== null) {
            $headers->clearHeaders();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRedirect(string $url, int $code = HttpResponse::STATUS_CODE_302): self
    {
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHttpResponseCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHeader(string $name, string $value, bool $replace = false): self
    {
        if ($replace) {
            $this->clearHeader($name);
        }
        /** @var Headers $headers */
        $headers = $this->getHeaders();
        $headers->addHeaderLine($name, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clearHeader(string $name): self
    {
        /** @var Headers $headers */
        $headers = $this->getHeaders();
        if ($headers->has($name)) {
            $headerValues = $headers->get($name);
            if (!is_iterable($headerValues)) {
                $headerValues = [$headerValues];
            }
            foreach ($headerValues as $headerValue) {
                $headers->removeHeader($headerValue);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sendResponse(): int|null
    {
        $this->send();
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getHeader(string $name): HeaderInterface|null
    {
        $header = null;
        $headers = $this->getHeaders();
        if ($headers === null) {
            return null;
        }
        if ($headers->has($name)) {
            $header = $headers->get($name);
            if (is_iterable($header)) {
                $header = $header[0];
            }
        }
        return $header;
    }

    /**
     * @inheritdoc
     */
    public function setStatusHeader(int|string $httpCode, int|string|null $version = null, string|null $phrase = null): HttpResponseInterface
    {
        $version = $version === null ? $this->detectVersion() : $version;
        $phrase = $phrase === null ? $this->getReasonPhrase() : $phrase;

        $this->setVersion($version);
        $this->setHttpResponseCode($httpCode);
        $this->setReasonPhrase($phrase);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function appendBody(string $value): HttpResponseInterface
    {
        $body = $this->getContent();
        $this->setContent($body . $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBody(string $value): HttpResponseInterface
    {
        $this->setContent($value);
        return $this;
    }

    /**
     * Clear body
     *
     * @return self
     */
    public function clearBody(): self
    {
        $this->setContent('');
        return $this;
    }
}
