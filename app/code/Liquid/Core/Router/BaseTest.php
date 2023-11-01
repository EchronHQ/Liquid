<?php

declare(strict_types=1);

namespace Liquid\Core\Router;

use Liquid\Core\Model\Request\Request;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    private Base $base;

    protected function setUp(): void
    {
        parent::setUp();
        $containerBuilder = new ContainerBuilder();
        $di = $containerBuilder->build();

        $this->base = new Base($di);
    }

    public function testMatchPaths(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                '' => 'SomeClass'
            ]
        ];
        $this->assertEquals('SomeClass', $this->base->getActionClassNew($module, '', new Request()));
        $this->assertNull($this->base->getActionClassNew($module, 'NonExisting', new Request()));
    }

    public function testParametersBasic(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                'actionA' => 'ClassForActionA',
                ':paramB' => 'ClassForActionWithParam',
            ]
        ];
        $this->assertEquals('ClassForActionA', $this->base->getActionClassNew($module, 'actionA', new Request()));
        $this->assertEquals('ClassForActionWithParam', $this->base->getActionClassNew($module, 'paramForActionB', new Request()));
    }

    public function testParametersHierarchy(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                ':actionId' => 'ClassForActionById',
                'category/:categoryId' => 'ClassForCategoryById',
                'type/:typeId' => 'ClassForTypeById',
                '' => 'ClassForOverview'
            ]
        ];
        $this->assertEquals('ClassForActionById', $this->base->getActionClassNew($module, 'actionA', new Request()));
        $this->assertEquals('ClassForCategoryById', $this->base->getActionClassNew($module, 'category/categoryA', new Request()));
    }
}
