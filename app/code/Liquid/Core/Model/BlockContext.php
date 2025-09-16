<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

use Liquid\Content\Helper\LocaleHelper;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Output;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\State;
use Liquid\Framework\View\Layout\Layout;
use Psr\Log\LoggerInterface;

readonly class BlockContext
{
    public function __construct(
        public ScopeConfig     $configuration,
        public Layout|null     $layout,
        public Resolver        $resolver,
        public FileHelper      $fileHelper,
        public LocaleHelper    $localeHelper,
        public Output          $outputHelper,
        public LoggerInterface $logger,
        public State           $appState,
    )
    {
    }
}
