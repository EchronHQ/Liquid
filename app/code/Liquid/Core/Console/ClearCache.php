<?php

declare(strict_types=1);

namespace Liquid\Core\Console;

use Liquid\Framework\App\Cache\CacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCache extends Command
{
    public function __construct(private readonly CacheManager $cacheManager)
    {
        parent::__construct('cache:clear');
        $this->setAliases(['cache:flush', 'cache:clean']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: loop over list of all cache types
        $cache = $this->cacheManager->clean([]);
//        $cleared = $cache->clean(CacheCleanMode::All);
//        if ($cleared) {
//            return Command::SUCCESS;
//        }

        return Command::FAILURE;


    }
}
