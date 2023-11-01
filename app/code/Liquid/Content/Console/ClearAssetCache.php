<?php

declare(strict_types=1);

namespace Liquid\Content\Console;

use Echron\Tools\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAssetCache extends Command
{
    public function __construct()
    {
        //assets:clear-cache|assets:clearcache
        parent::__construct('assets:clear-cache');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $cacheLocation = \ROOT . 'pub/frontend/cache/';
        if (!FileSystem::dirExists($cacheLocation)) {
            $output->writeln('Cache directory "' . $cacheLocation . '" does not exists');
            return Command::SUCCESS;
        }

        $files = FileSystem::listFiles($cacheLocation);
        foreach ($files as $file) {

            echo $file->getPathname() . ' - ' . $file->getFilename() . ' ' . $file->getType() . ' ' . $file->getSize() . PHP_EOL;
            if ($file->getPathname() !== '') {
                \unlink($file->getPathname());
                $output->writeln('Removed `' . $file->getFilename() . '`');
            }
        }


        $output->writeln('Removed ' . count($files) . ' files');
        return Command::SUCCESS;
    }
}
