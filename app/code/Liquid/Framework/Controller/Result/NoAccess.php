<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Laminas\Http\Response as ResponseAlias;

class NoAccess extends Error
{

    public function __construct(string $message = 'No access', int $statusCode = ResponseAlias::STATUS_CODE_403)
    {
        parent::__construct($message, $statusCode);
    }
}
