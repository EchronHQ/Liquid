<?php
declare(strict_types=1);

namespace Liquid\Framework\Output;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use voku\helper\HtmlMin;

class Html
{
    /**
     * https://github.com/voku/HtmlMin
     * @param string $html
     * @return string
     */
    public static function minify(string $html): string
    {
        $htmlMin = new HtmlMin();
        return $htmlMin->minify($html);
    }

    /**
     * https://github.com/matthiasmullie/minify
     * @param string $css
     * @return string
     */
    public static function minifyCss(string $css): string
    {
        $minifier = new CSS();
        $minifier->add($css);
        return $minifier->minify();
    }

    /**
     * https://github.com/matthiasmullie/minify
     * @param string $js
     * @return string
     */
    public static function minifyJs(string $js): string
    {
        $minifier = new JS();
        $minifier->add($js);
        return $minifier->minify();
    }
}
