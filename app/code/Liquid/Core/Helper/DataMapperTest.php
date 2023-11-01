<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    public function testHits(): void
    {
        $data = [
            'fieldA' => 'valueA'
        ];

        $mapper = new DataMapper($data);

        $this->assertEquals('valueA', $mapper->getProperty('fieldA'));

        $this->assertEquals([], $mapper->getNotUsedProperties());
    }

    public function testHitsWithMisses(): void
    {
        $data = [
            'fieldA' => 'valueA',
            'fieldB' => 'valueB'
        ];

        $mapper = new DataMapper($data);

        $this->assertEquals('valueA', $mapper->getProperty('fieldA'));

        $this->assertEquals(['fieldB'], $mapper->getNotUsedProperties());
    }
}
