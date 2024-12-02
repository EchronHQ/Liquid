<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller;

use Liquid\Framework\App\Response\ResponseInterface;

interface ResultInterface
{
    /**
     * @param int $httpCode
     * @return $this
     */
    public function setHttpResponseCode(int $httpCode): self;

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return $this
     */
    public function setHeader(string $name, string $value, bool $replace = false): self;

    /**
     * Render result and set to response
     *
     * @param ResponseInterface $response
     * @return ResultInterface
     */
    public function renderResult(ResponseInterface $response): ResultInterface;
}
