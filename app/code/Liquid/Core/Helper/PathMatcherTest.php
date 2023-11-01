<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use PHPUnit\Framework\TestCase;

class PathMatcherTest extends TestCase
{
    /**
     * TODO: what if 2 params have the same name?
     */
    public function testMatchesBasicPath(): void
    {
        $this->assertTrue(PathMatcher::matches('/:param', '/somepath'));
        $this->assertTrue(PathMatcher::matches(':param', 'somepath'));

        $this->assertFalse(PathMatcher::matches('/somepath', ''));
        $this->assertFalse(PathMatcher::matches('/:param', ''));
        $this->assertFalse(PathMatcher::matches(':param', ''));
        $this->assertTrue(PathMatcher::matches('/somepath', '/somepath'));
        $this->assertTrue(PathMatcher::matches('somepath', 'somepath'));
    }

    public function testMatchesParameters(): void
    {
        $this->assertFalse(PathMatcher::matches('/somepath/:id', ''));
        $this->assertFalse(PathMatcher::matches('/somepath/:id', '/somepath'));
        $this->assertTrue(PathMatcher::matches('/somepath/:id', '/somepath/12'));
    }

    public function testMatchesParametersDifferentLength(): void
    {
        $this->assertFalse(PathMatcher::matches('/page/:id', ''));
        $this->assertFalse(PathMatcher::matches('/page/:id', '/page'));
        $this->assertTrue(PathMatcher::matches('/page/:id', '/page/home'));
    }
}
