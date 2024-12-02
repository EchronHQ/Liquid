<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Response;

interface ResponseInterface
{
    /**
     * Send response to client
     *
     * @return int|null
     */
    public function sendResponse(): int|null;
}
