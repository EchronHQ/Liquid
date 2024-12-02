<?php
declare(strict_types=1);

namespace Liquid\Framework\Component;

use PHPUnit\Framework\TestCase;

class ComponentFileTest extends TestCase
{
    public function testExtractModule()
    {
        $details = ComponentFile::extractModule('somefile.phtml');
        $this->assertEquals(['moduleId' => '', 'filePath' => 'somefile.phtml'], $details);

        $details = ComponentFile::extractModule('Liquid_Core::somefile.phtml');
        $this->assertEquals(['moduleId' => 'Liquid_Core', 'filePath' => 'somefile.phtml'], $details);
    }
}
