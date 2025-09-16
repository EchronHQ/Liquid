<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;


use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

class RequestRewriteHelper
{
    public static function rewrite(UrlRewrite $rewrite, string $path): UrlRewrite|null
    {
        $arguments = PathMatcher::getMatchValues($rewrite->getRequestPath(), $path);

        if ($arguments === null) {
            return null;
        }
        $target = $rewrite->getTargetPath();
        foreach ($arguments as $key => $value) {
            $target = \str_replace($key, $value, $target);
        }

        $rewrite->setTargetPath($target);
        return $rewrite;
    }
}
