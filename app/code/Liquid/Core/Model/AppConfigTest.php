<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function testExisting(): void
    {
        $config = new AppConfig();
        $config->setValue('test', 'value');
        $config->setValue('test2', ['sub' => 'subvalue']);

        $this->assertEquals('value', $config->getValue('test'));
        $this->assertEquals('subvalue', $config->getValue('test2.sub'));
    }

    public function testNonExisting(): void
    {
        $config = new AppConfig();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Config value "test" not found');

        $this->assertEquals('default', $config->getValue('test'));
    }

    public function testNonExistingWithDefault(): void
    {
        $config = new AppConfig();


        $this->assertEquals('default', $config->getValue('test', 'default'));
        $this->assertEquals('defaultsub', $config->getValue('test.sub', 'defaultsub'));
    }

}
