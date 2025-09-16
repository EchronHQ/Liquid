<?php
declare(strict_types=1);

namespace Liquid\Framework\Tests;

use Liquid\Framework\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{


    public function testGenerateUrl(): void
    {
        $objectManager = new \Liquid\Framework\TestFramework\ObjectManager($this);

        $url = $objectManager->getObject(Url::class);
//$url = new Url()

    }
}
