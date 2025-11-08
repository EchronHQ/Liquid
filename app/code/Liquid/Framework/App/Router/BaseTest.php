<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use DI\ContainerBuilder;
use Liquid\Framework\App\Request\HttpRequest;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    private BaseRouter $base;

    protected function setUp(): void
    {
        parent::setUp();
        $containerBuilder = new ContainerBuilder();
        $di = $containerBuilder->build();

        $this->base = new BaseRouter($di);
    }

    public function testMatchPaths(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                '' => 'SomeClass',
            ],
        ];
        $this->assertEquals('SomeClass', $this->base->getActionClassNew($module, '', new HttpRequest()));
        $this->assertNull($this->base->getActionClassNew($module, 'NonExisting', new HttpRequest()));
    }

    public function testParametersBasic(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                'actionA' => 'ClassForActionA',
                ':paramB' => 'ClassForActionWithParam',
            ],
        ];
        $this->assertEquals('ClassForActionA', $this->base->getActionClassNew($module, 'actionA', new HttpRequest()));
        $this->assertEquals('ClassForActionWithParam', $this->base->getActionClassNew($module, 'paramForActionB', new HttpRequest()));
    }

    public function testParametersHierarchy(): void
    {
        $module = [
            'moduleName' => 'test',
            'actions' => [
                ':actionId' => 'ClassForActionById',
                'category/:categoryId' => 'ClassForCategoryById',
                'type/:typeId' => 'ClassForTypeById',
                '' => 'ClassForOverview',
            ],
        ];
        $this->assertEquals('ClassForActionById', $this->base->getActionClassNew($module, 'actionA', new HttpRequest()));
        $this->assertEquals('ClassForCategoryById', $this->base->getActionClassNew($module, 'category/categoryA', new HttpRequest()));
    }
}
