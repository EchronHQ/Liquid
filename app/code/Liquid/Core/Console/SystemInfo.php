<?php

declare(strict_types=1);

namespace Liquid\Core\Console;

use Liquid\Core\Model\AppConfig;
use Liquid\Seo\Helper\GenerateSitemapHelper;
use Echron\Tools\Bytes;
use Echron\Tools\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SystemInfo extends Command
{
    public function __construct(
        private readonly AppConfig $appConfig
    ) {
        parent::__construct('system:info');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $assetFileInfo = $this->getAssetCacheFileInfo();
        $data = [
            'Root directory' => ROOT,
            'Mode' => $this->appConfig->getMode()->name,
            'Site url' => $this->appConfig->getValue('site_url'),
            'Sitemap generation date' => $this->getSitemapGenerationDate(),
            'Asset cache files count' => $assetFileInfo['count'],
            'Asset cache files size' => Bytes::readable($assetFileInfo['size'], 2)
        ];


        $table = new Table($output);

        $table->setHeaders(['Key', 'Value']);

        foreach ($data as $key => $value) {
            $table->addRow([$key, $value]);
        }
        $table->render();

        return Command::SUCCESS;
    }

    private function getSitemapGenerationDate(): string
    {
        $sitemapAge = GenerateSitemapHelper::getModificationDate();
        return $sitemapAge === null ? 'not generated' : $sitemapAge->format("Y-m-d H:i:s");
    }

    private function getAssetCacheFileInfo(): array
    {
        $cacheLocation = \ROOT . 'pub/frontend/cache/';
        if (!FileSystem::dirExists($cacheLocation)) {
            return ['count' => 0, 'size' => 0];
        }

        $files = FileSystem::listFiles($cacheLocation);

        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }
        return ['count' => count($files), 'size' => $totalSize];
    }
}
