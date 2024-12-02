<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Console;

use Liquid\Framework\App\Response\ResponseInterface;

class Response implements ResponseInterface
{
    /**
     * Success code
     */
    public const SUCCESS = 0;
    /**
     * Error code
     */
    public const ERROR = 255;
    /**
     * Status code
     * Possible values:
     *  0 (successfully)
     *  1-255 (error)
     *  -1 (error)
     *
     * @var int
     */
    protected int $code = 0;
    /**
     * Text to output on send response
     *
     * @var string
     */
    private string $body = '';

    private bool $terminateOnSend = true;

    public function sendResponse(): int|null
    {
        if (!empty($this->body)) {
            echo $this->body;
        }
        if ($this->terminateOnSend) {
            exit($this->code);
        }
        return $this->code;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Set exit code
     *
     * @param int $code
     * @return void
     */
    public function setCode(int $code): void
    {
        if ($code > 255) {
            $code = 255;
        }
        $this->code = $code;
    }
}
