<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Request;

use Laminas\Http\Headers;

class Response extends \Laminas\Http\PhpEnvironment\Response
{
    public ResponseType $type;

    public function setHttpResponseCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $name, string $value, bool $replace = false): Response
    {
        if ($replace) {
            $this->clearHeader($name);
        }
        /** @var Headers $headers */
        $headers = $this->getHeaders();
        $headers->addHeaderLine($name, $value);
        return $this;
    }


    public function clearHeader(string $name): Response
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

    public function setRedirect(string $url, int $code = 302): Response
    {
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        return $this;
    }
}
