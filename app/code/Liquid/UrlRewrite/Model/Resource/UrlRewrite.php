<?php

declare(strict_types=1);

namespace Liquid\UrlRewrite\Model\Resource;

class UrlRewrite
{
    public string $request;
    public string $target;
    public UrlRewriteType $statusCode;

    public function __construct(string $request, string $target, UrlRewriteType $statusCode = UrlRewriteType::PERMANENT)
    {
        $this->request = ltrim($request, '/');
        $this->target = ltrim($target, '/');
        $this->statusCode = $statusCode;
    }
}
