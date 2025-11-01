<?php
declare(strict_types=1);

namespace Liquid\Framework;

class StringHelper
{
    /**
     * Default charset
     */
    public const string ICONV_CHARSET = 'UTF-8';

    /**
     * Clean non UTF-8 characters
     *
     * @param string $string
     * @return string
     */
    public function cleanString(string $string): string
    {
        return \mb_convert_encoding($string, self::ICONV_CHARSET);
    }
}
