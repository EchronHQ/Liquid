<?php
declare(strict_types=1);

namespace Liquid\Framework\TestFramework;

use PHPUnit\Framework\TestCase;

class ObjectManager
{
    /**
     * Constructor
     *
     * @param TestCase $testObject
     */
    public function __construct(protected readonly TestCase $testObject)
    {
    }

    public function getObject(string $className, array $arguments = [])
    {

    }
}
