<?php
declare(strict_types=1);

namespace Liquid\Framework;

use Liquid\Framework\Exception\ErrorHandler;
use Liquid\Framework\Exception\RuntimeException;

class Glob
{
    /**#@+
     * Glob constants.
     */
    public const int GLOB_MARK = 0x01;
    public const int GLOB_NOSORT = 0x02;
    public const int GLOB_NOCHECK = 0x04;
    public const  int GLOB_NOESCAPE = 0x08;
    public const int GLOB_BRACE = 0x10;
    public const int GLOB_ONLYDIR = 0x20;
    public const int GLOB_ERR = 0x40;


    public static function glob(string $pattern, int $flags): array|null
    {
        if ($flags) {
            $flagMap = [
                self::GLOB_MARK => GLOB_MARK,
                self::GLOB_NOSORT => GLOB_NOSORT,
                self::GLOB_NOCHECK => GLOB_NOCHECK,
                self::GLOB_NOESCAPE => GLOB_NOESCAPE,
                self::GLOB_BRACE => \defined('GLOB_BRACE') ? GLOB_BRACE : 0,
                self::GLOB_ONLYDIR => GLOB_ONLYDIR,
                self::GLOB_ERR => GLOB_ERR,
            ];

            $globFlags = 0;

            foreach ($flagMap as $internalFlag => $globFlag) {
                if ($flags & $internalFlag) {
                    $globFlags |= $globFlag;
                }
            }
        } else {
            $globFlags = 0;
        }

        ErrorHandler::start();
        $res = \glob($pattern, $globFlags);
        $err = ErrorHandler::stop();
        if ($res === false) {
            throw new RuntimeException("glob('{$pattern}', {$globFlags}) failed", 0, $err);
        }
        return $res;
    }
}
