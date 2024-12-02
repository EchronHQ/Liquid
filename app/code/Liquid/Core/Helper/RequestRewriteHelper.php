<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;


use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

class RequestRewriteHelper
{
    public static function rewrite(UrlRewrite $rewrite, string $path): UrlRewrite|null
    {
        $arguments = PathMatcher::getMatchValues($rewrite->request, $path);

        if ($arguments === null) {
            return null;
        }
        $target = $rewrite->target;
        foreach ($arguments as $key => $value) {
            $target = \str_replace($key, $value, $target);
        }

        $rewrite->target = $target;
        return $rewrite;
    }
}
