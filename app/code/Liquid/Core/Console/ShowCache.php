<?php

declare(strict_types=1);

namespace Liquid\Core\Console;

use Liquid\Core\Helper\CacheHelper;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCache extends Command
{
    public function __construct(
        private readonly CacheHelper $cache
    ) {
        parent::__construct('cache:show');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $keys = $this->cache->getKeys();
        //        $items = $this->cache->getItems($keys);


        $table = new Table($output);

        $table->setHeaders(['key', 'value', 'expiration', 'hit']);
        if (count($keys) === 0) {
            $table->addRow(['No items']);
        }


        foreach ($keys as $key) {
            $item = $this->cache->getItem($key);

            $expiration = $this->formatExpiration($item);
            $value = $this->formatValue($item);
            $table->addRow([$item->getKey(), $value, $expiration, $item->isHit() ? 'Y' : 'N']);
        }
        $table->render();

        return Command::SUCCESS;
    }

    private function formatValue(CacheItemInterface $item): string
    {
        $value = $item->get();
        // TODO: get datatype
        $value = (string)$value;
        if (\strlen($value) > 100) {
            $value = \substr($value, 0, 100) . '...';
        }

        return $value;
    }

    private function formatExpiration(CacheItemInterface $item): string
    {
        //        $expirationTimestamp = null;//$item->get();

        //        if ($expirationTimestamp === null) {
        //            return 'never';
        //        }
        //        if (\is_int($expirationTimestamp)) {
        //            $now = new \DateTime();
        //            $expiration = new \DateTime();
        //            $expiration->setTimestamp($item->getExpirationTimestamp());
        //            $interval = $expiration->getTimestamp() - $now->getTimestamp();
        //
        //            return 'In ' . Time::readableSeconds($interval);
        //        }
        return 'unknown';
    }
}
