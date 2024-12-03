<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

use Liquid\Framework\App\Config\SegmentConfig;
use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function testExisting(): void
    {
        $config = new SegmentConfig();
        $config->setValue('test', 'value');
        $config->setValue('test2', ['sub' => 'subvalue']);

        $this->assertEquals('value', $config->getValue('test'));
        $this->assertEquals('subvalue', $config->getValue('test2.sub'));
    }

    public function testNonExisting(): void
    {
        $config = new SegmentConfig();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Config value "test" not found');

        $this->assertEquals('default', $config->getValue('test'));
    }

    public function testNonExistingWithDefault(): void
    {
        $config = new SegmentConfig();


        $this->assertEquals('default', $config->getValue('test', 'default'));
        $this->assertEquals('defaultsub', $config->getValue('test.sub', 'defaultsub'));
    }

}
