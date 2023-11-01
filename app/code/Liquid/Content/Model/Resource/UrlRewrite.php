<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

class UrlRewrite
{
    public string $request;
    public string $target;
    public UrlRewriteType $statusCode;

    public function __construct(string $request, string $target, UrlRewriteType $statusCode = UrlRewriteType::PERMANENT)
    {
        $this->request = $request;
        $this->target = $target;
        $this->statusCode = $statusCode;
    }
}
