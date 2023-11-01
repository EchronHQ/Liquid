<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    public function testEscapeHtmlAttribute(): void
    {
        $output = new Output();
        $this->assertEquals('Escaped value', $output->escapeHtmlAttribute('Escaped value'));
        $this->assertEquals('Escaped value', $output->escapeHtmlAttribute('Escaped <a href="#">value</a>'));
        $this->assertEquals('Escaped value', $output->escapeHtmlAttribute('Escaped {TERM}value{/TERM}'));
    }


}
