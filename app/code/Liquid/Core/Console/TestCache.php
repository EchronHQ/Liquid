<?php

declare(strict_types=1);

namespace Liquid\Core\Console;

use Liquid\Core\Helper\CacheHelper;
use Echron\Tools\StringHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCache extends Command
{
    public function __construct(
        private readonly CacheHelper $cache
    ) {
        parent::__construct('cache:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $key = 'test-' . StringHelper::generateGuid();
        $value = StringHelper::generateRandom(2048);

        $has = $this->cache->has($key);
        $output->writeln('Cache has: ' . ($has ? 'Error' : 'Ok'));

        $set = $this->cache->set($key, $value);
        $output->writeln('Cache set: ' . ($set ? 'Ok' : 'Error'));


        $get = $this->cache->get($key);
        $output->writeln('Cache get: ' . ($value === $get ? 'Ok' : 'Error'));

        $has2 = $this->cache->has($key);
        $output->writeln('Cache has2: ' . ($has2 ? 'Ok' : 'Error'));

        $keys = $this->cache->getKeys();

        $output->writeln('Cache keys: ' . (in_array($key, $keys, true) ? 'Ok' : 'Error'));

        $unset = $this->cache->unset($key);
        $output->writeln('Cache unset: ' . ($unset ? 'Ok' : 'Error'));

        $has3 = $this->cache->has($key);
        $output->writeln('Cache has3: ' . ($has3 ? 'Error' : 'Ok'));

        return Command::SUCCESS;
    }


}
