<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Response;

enum HttpResponseCode: int
{
    case STATUS_CODE_301 = 301;
    case STATUS_CODE_302 = 302;
    case STATUS_CODE_403 = 403;
    case NOT_FOUND = 404;
}
