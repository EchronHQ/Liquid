<?php
declare(strict_types=1);

namespace Liquid\Framework\Tests;

use Liquid\Content\Model\Resolver\Segment;
use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Content\Model\SegmentRepository;
use Liquid\Content\Model\SegmentResolver;
use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Config\SegmentConfigInterface;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Scope\ScopeCodeResolver;
use Liquid\Framework\Escaper;
use Liquid\Framework\ObjectManager\ObjectManager;
use Liquid\Framework\Serialize\Serializer\Serialize;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\StringHelper;
use Liquid\Framework\Url;
use Liquid\Framework\Url\QueryParamsResolver;
use Liquid\Framework\Url\RouteParamsResolver;
use Liquid\Framework\Url\ScopeResolver;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UrlTest extends TestCase
{
    private Url $urlHelper;

    public function testGetEmptyUrl(): void
    {

        // Empty Url
        $this->assertEquals('https://lq.com', $this->urlHelper->getUrl());
        $this->assertEquals('https://lq.com', $this->urlHelper->getUrl(''));

    }

    public function getNormalUrl(): void
    {

        // Url with single segment
        $this->assertEquals('https://lq.com/blog', $this->urlHelper->getUrl('blog'));
        $this->assertEquals('https://lq.com/blog', $this->urlHelper->getUrl('/blog'));
        $this->assertEquals('https://lq.com/blog', $this->urlHelper->getUrl('/blog/'));
        $this->assertEquals('https://lq.com/blog', $this->urlHelper->getUrl('blog/'));

        $this->assertEquals('https://lq.com/blog/category/categorya', $this->urlHelper->getUrl('blog/category/categoryA'));
    }

    protected function setUp(): void
    {
        $objectManagerConfig = new \Liquid\Framework\ObjectManager\Config();
        $objectManagerConfig->extend(['preferences' => [
            \Liquid\Framework\Url\ScopeResolverInterface::class => \Liquid\Framework\Url\ScopeResolver::class,
            \Liquid\Framework\App\Scope\ScopeResolverInterface::class => \Liquid\Content\Model\Resolver\Segment::class,
            SegmentConfigInterface::class => ScopeConfig::class,
        ]]);

        $logger = new Logger('Unit test');
        $serializer = new Serialize();
        $sharedInstances = [
            \Liquid\Framework\Filesystem\DirectoryList::class => new \Liquid\Framework\Filesystem\DirectoryList(''),
            LoggerInterface::class => $logger,
            SerializerInterface::class => $serializer,
        ];
        $objectManager = new ObjectManager($objectManagerConfig, $sharedInstances);


        $scopeCodeResolver = new ScopeCodeResolver();
        $segmentConfig = $objectManager->get(SegmentConfigInterface::class);


        $segmentResolver = new SegmentResolver($objectManager);

        $segmentRepository = new SegmentRepository($objectManager);
        $profiler = new Profiler();
        $segmentManager = new SegmentManager($segmentConfig, $segmentRepository, $segmentResolver, $profiler);
        $s = new Segment($segmentManager);
        $urlScopeResolver = new ScopeResolver($s);
        $queryParamsResolver = new QueryParamsResolver();

        $converter = new StringHelper();
        $request = new Request($converter);
        $escaper = new Escaper($logger);
        $routeParamsResolver = new RouteParamsResolver($request, $queryParamsResolver, $escaper);


        $data = [];

        $this->urlHelper = new Url($segmentConfig, $urlScopeResolver, $routeParamsResolver, $queryParamsResolver, $request, $serializer, $escaper, $logger, $data);

    }
}
