<?php

declare(strict_types=1);

namespace Liquid\Framework\App\Response;

use Liquid\Core\Model\Request\ResponseType;

class Response implements HttpResponseInterface
{
    public ResponseType $type;
    protected HttpResponseCode $statusCode;
    /** @var \Closure */
    private $headersSentHandler;

    private \Symfony\Component\HttpFoundation\Response $response;

    public function __construct()
    {
        $this->response = new \Symfony\Component\HttpFoundation\Response();

        $this->headersSentHandler = static function ($response) {
            throw new \RuntimeException('Cannot send headers, headers already sent');
        };
        // parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getHttpResponseCode(): HttpResponseCode
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
        $headers = $this->response->headers->all();
        foreach ($headers as $key => $header) {
            $this->response->headers->remove($key);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRedirect(string $url, HttpResponseCode $code = HttpResponseCode::STATUS_CODE_302): self
    {
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHttpResponseCode(HttpResponseCode $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHeader(string $name, string $value, bool $replace = false): self
    {
//        if ($replace) {
//            $this->clearHeader($name);
//        }

        $this->response->headers->set($name, $value, $replace);
//        /** @var Headers $headers */
//        $headers = $this->getHeaders();
//        $headers->addHeaderLine($name, $value);
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->response->headers->all();
    }

    /**
     * @inheritdoc
     */
    public function clearHeader(string $name): self
    {
        $this->response->headers->remove($name);
//        $headers = $this->getHeaders();
//        if ($headers->has($name)) {
//            $headerValues = $headers->get($name);
//            if (!is_iterable($headerValues)) {
//                $headerValues = [$headerValues];
//            }
//            foreach ($headerValues as $headerValue) {
//                $headers->removeHeader($headerValue);
//            }
//        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function sendResponse(): int|null
    {
//        $this->setHeadersSentHandler(function () {
//            echo 'headers already send';
//        });
        $this->response->sendHeaders();
        $this->response->send();
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getHeader(string $name): string|null
    {
        // $header = null;

        if (!$this->response->headers->has($name)) {
            return null;
        }

        $header = $this->response->headers->get($name);
//        $header = $headers->get($name);
//        if (is_iterable($header)) {
//            $header = $header[0];
//        }

        return $header;
    }

    /**
     * @inheritdoc
     */
    public function setStatusHeader(HttpResponseCode|string $httpCode, int|string|null $version = null, string|null $phrase = null): HttpResponseInterface
    {
        $version = $version === null ? $this->response->getProtocolVersion() : $version;

        //   $version = $version === null ? $this->detectVersion() : $version;
        //   $phrase = $phrase === null ? $this->getReasonPhrase() : $phrase;


        $this->response->setProtocolVersion($version);

        $this->setHttpResponseCode($httpCode);

        $this->response->setStatusCode($httpCode->value, $phrase);
        //  $this->setReasonPhrase($phrase);


        return $this;
    }

    /**
     * @inheritdoc
     */
    public function appendBody(string $value): HttpResponseInterface
    {
        $body = $this->response->getContent();
        if ($body === false) {
            $body = '';
        }
        $this->response->setContent($body . $value);
        return $this;
    }

    public function getBody(): string
    {
        $body = $this->response->getContent();
        if ($body === false) {
            return '';
        }
        return $body;
    }

    /**
     * @inheritdoc
     */
    public function setBody(string $value): HttpResponseInterface
    {
        $this->response->setContent($value);
        return $this;
    }

    /**
     * Clear body
     *
     * @return self
     */
    public function clearBody(): self
    {
        $this->response->setContent(null);
        return $this;
    }
}
