<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Config\SegmentConfig;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class TerminologyHelperTest extends TestCase
{
    public function testTextWithoutTerms(): void
    {

        $stub = $this->createStub(TerminologyRepository::class);
        $resolver = $this->createStub(Resolver::class);
        $appConfig = $this->createStub(SegmentConfig::class);
        $logger = $this->createStub(Logger::class);

        $terminologyHelper = new TerminologyHelper($stub, $resolver, $appConfig, $logger);

        $this->assertEquals('', $terminologyHelper->buildTerms(''));
        $this->assertEquals('Text without terms', $terminologyHelper->buildTerms('Text without terms'));
    }

    public function testTextWithTermsNotFound(): void
    {
        $stub = $this->createStub(TerminologyRepository::class);
        $resolver = $this->createStub(Resolver::class);
        $appConfig = $this->createStub(SegmentConfig::class);
        $logger = $this->createStub(Logger::class);

        $terminologyHelper = new TerminologyHelper($stub, $resolver, $appConfig, $logger);

        $this->assertEquals('This should not explain what an unknownterm is.', $terminologyHelper->buildTerms('This should not explain what an {TERM}unknownterm{/TERM} is.'));
    }

    public function testTextWithTerms(): void
    {
        $stub = $this->createStub(TerminologyRepository::class);
        $resolver = $this->createStub(Resolver::class);
        $appConfig = $this->createStub(SegmentConfig::class);
        $logger = $this->createStub(Logger::class);

        $terminologyHelper = new TerminologyHelper($stub, $resolver, $appConfig, $logger);

        $terminologyHelper->addTerm('term', '#term');
        $terminologyHelper->addTerm('explain', '#explain');

        $this->assertEquals('This should explain what a <a href="#term" class="link term intext">term</a> is.', $terminologyHelper->buildTerms('This should explain what a {TERM}term{/TERM} is.'));
        $this->assertEquals('This should <a href="#explain" class="link term intext">explain</a> what a <a href="#term" class="link term intext">term</a> is.', $terminologyHelper->buildTerms('This should {TERM}explain{/TERM} what a {TERM}term{/TERM} is.'));
    }
}
