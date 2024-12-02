<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;


use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use PHPUnit\Framework\TestCase;

class RequestRewriteHelperTest extends TestCase
{
    public function testMatchesBasicPath(): void
    {
        $rewrite = new UrlRewrite('/somepath', '/targetpath');
        $this->assertEquals('/targetpath', RequestRewriteHelper::rewrite($rewrite, '/somepath')->target);
    }

    public function testMatchesParameters(): void
    {
        $rewrite = new UrlRewrite('/somepath/:id', '/targetpath/:id');

        $this->assertNull(RequestRewriteHelper::rewrite($rewrite, '/notmatching/12'));
        $this->assertEquals('/targetpath/12', RequestRewriteHelper::rewrite($rewrite, '/somepath/12')->target);
    }

    public function testMatchesParametersDifferentLength(): void
    {
        $rewrite = new UrlRewrite('/page/:id', '/page/view/page/:id');
        $this->assertNull(RequestRewriteHelper::rewrite($rewrite, '/blog/12'));

        $this->assertEquals('/page/view/page/home', RequestRewriteHelper::rewrite($rewrite, '/page/home')->target);
    }
}
