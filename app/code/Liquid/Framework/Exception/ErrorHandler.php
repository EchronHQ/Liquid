<?php
declare(strict_types=1);

namespace Liquid\Framework\Exception;

use ErrorException;


class ErrorHandler
{
    /**
     * Active stack
     *
     * @var list<ErrorException|null>
     */
    private static array $stack = [];

    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     * @return void
     */
    public static function start(int $errorLevel = E_WARNING): void
    {
        if (!static::$stack) {
            \set_error_handler([static::class, 'addError'], $errorLevel);
        }

        static::$stack[] = null;
    }

    /**
     * Stopping the error handler
     *
     * @param bool $throw Throw the ErrorException if any
     * @return null|ErrorException
     * @throws ErrorException If an error has been caught and $throw is true.
     */
    public static function stop(bool $throw = false): null|ErrorException
    {
        $errorException = null;

        if (static::$stack) {
            $errorException = \array_pop(static::$stack);

            if (!static::$stack) {
                \restore_error_handler();
            }

            if ($errorException && $throw) {
                throw $errorException;
            }
        }

        return $errorException;
    }

    /**
     * Add an error to the stack
     *
     * @param int $errno
     * @param string $message
     * @param string $filename
     * @param int $line
     * @return void
     */
    public static function addError(int $errno, string $message = '', string $filename = '', int $line = 0): void
    {
        $stack = &static::$stack[\count(static::$stack) - 1];
        $stack = new ErrorException($message, 0, $errno, $filename, $line, $stack);
    }
}
