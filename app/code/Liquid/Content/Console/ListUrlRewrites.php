<?php

declare(strict_types=1);

namespace Liquid\Content\Console;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\UrlRewrite\Model\AggregateUrlFinder;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUrlRewrites extends Command
{
    public function __construct(
        private readonly AggregateUrlFinder $urlFinder,
        private readonly SegmentManager     $segmentManager
    )
    {
        parent::__construct('content:list-url-rewrites');
        //$this->setAliases(['seo:sitemap-draw']);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $segment = $this->segmentManager->getSegment(new SegmentId('seg_0'));
        var_dump($this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => 'use-cases/ecommerce', UrlRewrite::SEGMENT_ID => $segment->id]));
        return Command::SUCCESS;
    }


}
