<?php

declare(strict_types=1);

namespace Liquid\Core\Console;

use Liquid\Core\Helper\CacheHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCache extends Command
{
    public function __construct(private readonly CacheHelper $cache)
    {
        parent::__construct('cache:clear');
        $this->setAliases(['cache:flush', 'cache:clean']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $cleared = $this->cache->clear();
        if ($cleared) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;


    }
}
