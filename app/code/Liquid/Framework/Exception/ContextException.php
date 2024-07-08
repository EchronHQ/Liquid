<?php
declare(strict_types=1);

namespace Liquid\Framework\Exception;

class ContextException extends \Exception
{
    public function __construct($message, public array $context = [], $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
