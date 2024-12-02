<?php

declare(strict_types=1);

namespace Liquid\Content\Console;

use Liquid\Content\Helper\StaticContentHelper;
use Liquid\Framework\App\Cache\CacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildStatic extends Command
{
    public function __construct(
        private readonly StaticContentHelper $staticContentHelper,
        private readonly CacheManager        $cache
    )
    {
        parent::__construct('setup:static:build');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        /**
         * Clear cache
         * Run npm run build
         * Generate sitemap
         */
        $output->writeln('Clear cache');
        // TODO: implement clean method
        $this->cache->clean([]);
        $output->writeln('Update static deploy version');
        $this->staticContentHelper->updateStaticDeployedVersion();
        $output->writeln('Build static content');
        exec('npm run build');
        // TODO: If build complete, remove old build

        return Command::SUCCESS;
    }
}
