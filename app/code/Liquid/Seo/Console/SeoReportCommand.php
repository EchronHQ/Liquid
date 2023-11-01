<?php

declare(strict_types=1);

namespace Liquid\Seo\Console;

use Liquid\Seo\Helper\ReportHelper;
use Liquid\Seo\Model\DownloadedPage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeoReportCommand extends Command
{
    public function __construct(private readonly ReportHelper $reportHelper)
    {
        parent::__construct('seo:report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        //$page = new SitemapUrlEntry('https://attlaz.com/platforms/shopware', PageSitemapPriority::LOW);
        //$pages = [$page];
        //        $output->writeln('TextToHtml' . $x->getTextToHtmlRatio() . ' txt ' . $x->getTextCharacterCount() . ' ' . $x->getHtmlCharacterCount());
        //        $output->writeln('Words ' . $x->getWordCount());
        //        return 0;
        //        // TODO: use site map for this
        $pages = $this->reportHelper->getAllPages('https://attlaz.com/sitemap.xml');
//        $pages = array_slice($pages, 0, 5, true);
        $issues = $this->reportHelper->generate($pages);

        $output->writeln(['Issues with ' . count($issues) . '/' . count($pages) . ' pages', '================']);

        //

        foreach ($issues as $issue) {


            /** @var DownloadedPage $page */
            $page = $issue['page'];
            /** @var array $pageIssues */
            $pageIssues = $issue['issues'];

            $output->writeln($page->getUrl());
            $table = new Table($output);
            foreach ($pageIssues as $pageIssue) {
                $table->addRow([$pageIssue]);
                //                $output->writeln('- ' . $pageIssue . '');
            }
            $table->render();

        }

        //


        return Command::SUCCESS;
    }
}
