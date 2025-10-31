<?php

declare(strict_types=1);

namespace Liquid\Core\Console;


use Echron\Tools\Bytes;
use Echron\Tools\FileSystem;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Repository\UrlRepository;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\State;
use Liquid\Framework\Email\SMTP\SMTPFactory;
use Liquid\Framework\Filesystem\Path;
use Liquid\Seo\Helper\GenerateSitemapHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SystemInfo extends Command
{
    public function __construct(
        private readonly ScopeConfig   $appConfig,
        private readonly State         $appState,
        private readonly UrlRepository $urlRepository,
        private readonly SMTPFactory   $SMTPFactory,
        private readonly Resolver      $resolver
    )
    {
        parent::__construct('system:info');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $assetFileInfo = $this->getAssetCacheFileInfo();
        $databaseInfo = $this->urlRepository->getTestFromDB();
        $testEmail = $this->testSMTP();
        $data = [
            'Root directory' => $this->resolver->getPath(Path::ROOT),
            'Pub directory' => $this->resolver->getPath(Path::PUB),
            'Mode' => $this->appState->getMode()->name,
            // TODO: this is wrong! Use the different sections instead
            'Site url' => $this->appConfig->getValue('site_url'),
            'Sitemap generation date' => $this->getSitemapGenerationDate(),
            'Asset cache files count' => $assetFileInfo['count'],
            'Asset cache files size' => Bytes::readable($assetFileInfo['size'], 2),
            'Database' => $databaseInfo['name'],
        ];
        // site_url
        // TODO: show smpt config + test sending of email (should not happen here but in a separate command)
        // TODO: test database config
        //  $this->baseRepository->getInfo();

        // $this->urlRepository->getTestFromDB();

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
        $sitemapAge = GenerateSitemapHelper::getModificationDate($this->resolver->getPath(Path::PUB, 'sitemap.xml'));
        return $sitemapAge === null ? 'not generated' : $sitemapAge->format("Y-m-d H:i:s");
    }

    private function getAssetCacheFileInfo(): array
    {
        $cacheLocation = $this->resolver->getPath(Path::MEDIA, 'cache');
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

    private function testSMTP(): array
    {
        $email = new Email()
            ->from(new Address('no-reply@attlaz.com', 'Attlaz'))
            ->to('hello@attlaz.com')
//            ->addTo('bar@example.com')
//            ->cc('cc@example.com')
//            ->addCc('cc2@example.com')
            ->text('Lorem ipsum...')
            ->html('This is a test email from Liquid')
            ->subject('Test email - Liquid');


        $smtp = $this->SMTPFactory->create();


        $result = $smtp->send($email);


        return [
            'status' => 'success',
        ];
    }
}
