<?php

declare(strict_types=1);

namespace Liquid\Seo\Console;

use Liquid\Content\Model\SitemapUrlEntry;
use Liquid\Content\Repository\LocaleRepository;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\Filesystem\Path;
use Liquid\Seo\Helper\GatherPages;
use Liquid\Seo\Helper\GenerateSitemapHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapGenerateCommand extends Command
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly Resolver         $resolver,
        private readonly GatherPages      $gatherPages
    )
    {
        parent::__construct('seo:generate-sitemap');
        $this->setAliases(['seo:sitemap-generate']);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pages = $this->gatherPages->getAllPages();

        $entries = [];

        $locales = $this->localeRepository->getAll();

        $defaultLocale = $this->localeRepository->getDefault();
        foreach ($pages as $page) {

            //$output->writeln($page->metaTitle . ' | ' . $page->getUrlPath());


            $entry = new SitemapUrlEntry($this->resolver->getUrl($page->getUrlPath()), $page->priority, $page->changeFrequency);
            $entry->lastmod = $page->modifiedDate;
            foreach ($locales as $locale) {
                if ($locale->code !== $defaultLocale->code) {
                    $alternativeUrl = $this->resolver->getUrl($page->getUrlPath(), $locale);
                    $entry->addAlternative($locale->langCode, $alternativeUrl);
                }
            }
            $entries[] = $entry;


        }

        $sitemapHelper = new GenerateSitemapHelper();
        $sitemapXml = $sitemapHelper->generate($entries);

        GenerateSitemapHelper::store($sitemapXml, $this->resolver->getPath(Path::PUB,'sitemap.xml'));

        $output->writeln('Sitemap generated (' . count($entries) . ') entries');
        return Command::SUCCESS;
    }
}
