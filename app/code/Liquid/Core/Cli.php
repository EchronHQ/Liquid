<?php

declare(strict_types=1);

namespace Liquid\Core;

use Liquid\Content\Console\BuildStatic;
use Liquid\Content\Console\ClearAssetCache;
use Liquid\Content\Repository\LocaleRepository;
use Liquid\Core\Console\ClearCache;
use Liquid\Core\Console\ShowCache;
use Liquid\Core\Console\SystemInfo;
use Liquid\Core\Console\TestCache;
use Liquid\Seo\Console\SeoReportCommand;
use Liquid\Seo\Console\SitemapDrawCommand;
use Liquid\Seo\Console\SitemapGenerateCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

class Cli extends Application
{
    public function run(): void
    {
        $start = \microtime(true);
        $this->beforeRun();

        /** @var LocaleRepository $localeRepository */
        $localeRepository = $this->getContainer()->get(LocaleRepository::class);
        $this->getConfig()->setLocale($localeRepository->getDefault(), false);

        $consoleApplication = new ConsoleApplication();

        $consoleApplication->add($this->getContainer()->get(SeoReportCommand::class));
        $consoleApplication->add($this->getContainer()->get(SitemapGenerateCommand::class));
        $consoleApplication->add($this->getContainer()->get(SitemapDrawCommand::class));

        $consoleApplication->add($this->getContainer()->get(ClearCache::class));
        $consoleApplication->add($this->getContainer()->get(ShowCache::class));
        $consoleApplication->add($this->getContainer()->get(TestCache::class));

        $consoleApplication->add($this->getContainer()->get(ClearAssetCache::class));
        $consoleApplication->add($this->getContainer()->get(BuildStatic::class));

        $consoleApplication->add($this->getContainer()->get(SystemInfo::class));

        $consoleApplication->run();
    }
}
