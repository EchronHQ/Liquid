<?php
declare(strict_types=1);

namespace Liquid\Framework\TestFramework;

class ObjectManager
{
    /**
     * Constructor
     *
     * @param \PHPUnit\Framework\TestCase $testObject
     */
    public function __construct(protected readonly \PHPUnit\Framework\TestCase $testObject)
    {
    }

    public function getObject(string $className, array $arguments = [])
    {

    }
}
