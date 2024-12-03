<?php

declare(strict_types=1);

namespace Liquid\Seo\Console;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Seo\Helper\GatherPages;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapDrawCommand extends Command
{
    public function __construct(
        private readonly GatherPages $gatherPages
    )
    {
        parent::__construct('seo:draw-sitemap');
        $this->setAliases(['seo:sitemap-draw']);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pages = $this->gatherPages->getAllPages();


        $rootSegment = new Segment('');
        foreach ($pages as $page) {

            if ($page->getUrlPath() === '') {
                $pathSegments = ['home'];
            } else {
                $pathSegments = explode('/', 'home/' . $page->getUrlPath());
            }


            $currentEntrySegment = $rootSegment;
            $level = 0;
            foreach ($pathSegments as $pathSegment) {
                //                if ($currentEntrySegment === null) {
                //                    $currentEntrySegment = ['title' => 'Unknown - ' . $pathSegment, 'url' => 'Unknown', 'children' => []];
                //
                //                    $entries[$pathSegment] = $currentEntrySegment;
                //                }


                if ($currentEntrySegment->hasChild($pathSegment)) {
                    if ($level === count($pathSegments) - 1) {
                        $x = $currentEntrySegment->getChild($pathSegment);
                        if (!$x->hasPage()) {
                            $x->setPage($page);
                        } else {
                            // This should not happen
                            echo 'WRONG' . PHP_EOL;
                        }
                    }

                } elseif ($level === count($pathSegments) - 1) {
                    $currentEntrySegment->setChild($pathSegment, new Segment($pathSegment, $page));
                } else {
                    $currentEntrySegment->setChild($pathSegment, new Segment($pathSegment));
                }

                $currentEntrySegment = $currentEntrySegment->getChild($pathSegment);


                $level++;
            }
        }

        $this->output($rootSegment);


        return Command::SUCCESS;
    }

    /**
     * @param Segment $segment
     * @param int $level
     * @return void
     */
    private function output(Segment $segment, int $level = 0): void
    {


        //echo $pre . $segment->pathSegment . str_repeat(' ', 50 - $x) . ' ' . $segment->getTitle() . PHP_EOL;

        $i = 0;
        foreach ($segment->getChildren() as $key => $childSegment) {

            $pre = '';


            if ($level > 1) {
                if ($i === count($segment->getChildren()) - 1) {
                    $pre = '└';
                } else {
                    $pre = '├';
                }
                $pre = str_repeat("  ", $level) . ' ' . $pre;
            }

            $x = strlen($childSegment->pathSegment) + strlen($pre);

            echo $pre . $childSegment->pathSegment . str_repeat(' ', 50 - $x) . ' ' . $childSegment->getTitle() . ' [' . $childSegment->getPath() . ']' . PHP_EOL;
            $i++;

            $this->output($childSegment, $level + 1);
        }
    }
}

class Segment
{
    private array $children = [];

    public function __construct(
        public string                       $pathSegment,
        private AbstractViewableEntity|null $page = null
    )
    {

    }

    public function getTitle(): string
    {
        if ($this->page !== null) {
            return $this->page->metaTitle;
        }
        return '[Unknown]';
    }

    public function getPath(): string
    {
        if ($this->page !== null) {
            return $this->page->getUrlPath();
        }
        return '[Unknown]';
    }

    public function setChild(string $key, Segment $child): void
    {
        $this->children[$key] = $child;
    }

    public function hasChild(string $key): bool
    {
        return array_key_exists($key, $this->children);
    }

    public function getChild(string $key): Segment
    {
        return $this->children[$key];
    }

    /**
     * @return Segment[]
     */
    public function getChildren(): array
    {
        return array_values($this->children);
    }

    public function getPage(): AbstractViewableEntity|null
    {
        return $this->page;
    }

    public function setPage(AbstractViewableEntity $page): void
    {
        $this->page = $page;
    }

    public function hasPage(): bool
    {
        return $this->page !== null;
    }
}
