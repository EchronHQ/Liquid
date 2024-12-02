<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Response;

use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Response as HttpResponse;

interface HttpResponseInterface extends ResponseInterface
{
    /**
     * Set HTTP response code
     *
     * @param int $code
     * @return self
     */
    public function setHttpResponseCode(int $code): self;

    /**
     * Get HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode(): int;

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that $name.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return self
     */
    public function setHeader(string $name, string $value, bool $replace = false): self;

    /**
     * Get header value by name
     *
     * Returns first found header by passed name.
     * If header with specified name was not found returns false.
     *
     * @param string $name
     * @return HeaderInterface|null
     */
    public function getHeader(string $name): HeaderInterface|null;

    /**
     * Remove header by name from header stack
     *
     * @param string $name
     * @return self
     */
    public function clearHeader(string $name): self;

    /**
     * Allow granular setting of HTTP response status code, version and phrase
     *
     * For example, a HTTP response as the following:
     *     HTTP 200 1.1 Your response has been served
     * Can be set with the arguments
     *     $httpCode = 200
     *     $version = 1.1
     *     $phrase = 'Your response has been served'
     *
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return self
     */
    public function setStatusHeader(int|string $httpCode, null|int|string $version = null, string|null $phrase = null): self;

    /**
     * Append the given string to the response body
     *
     * @param string $value
     * @return self
     */
    public function appendBody(string $value): self;

    /**
     * Set the response body to the given value
     *
     * Any previously set contents will be replaced by the new content.
     *
     * @param string $value
     * @return self
     */
    public function setBody(string $value): self;

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior redirects.
     *
     * @param string $url
     * @param int $code
     * @return self
     */
    public function setRedirect(string $url, int $code = HttpResponse::STATUS_CODE_302): self;
}
