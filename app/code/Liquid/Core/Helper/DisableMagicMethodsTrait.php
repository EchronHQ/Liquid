<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

trait DisableMagicMethodsTrait
{
    public function __get(string $name): void
    {
        //        \var_dump(\debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS));
        //        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        throw new \Exception('Magic get is disabled (' . $name . ')');
    }

    public function __set(string $name, mixed $value): void
    {
        throw new \Exception('Magic set is disabled (' . $name . ')');
    }

    public function __isset(string $name): bool
    {
        throw new \Exception('Magic isset is disabled (' . $name . ')');
    }
}
