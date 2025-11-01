<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseCode;

class NoAccess extends Error
{

    public function __construct(string $message = 'No access', HttpResponseCode $statusCode = HttpResponseCode::STATUS_CODE_403)
    {
        parent::__construct($message, $statusCode);
    }
}
