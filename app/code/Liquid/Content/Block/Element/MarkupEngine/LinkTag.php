<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;

use Liquid\Content\Helper\ViewableEntity;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\State;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\TemplateEngine;
use Psr\Log\LoggerInterface;

class LinkTag extends Template
{
    protected string|null $template = 'Liquid_Content::element/markupengine/link.phtml';

    public function __construct(
        Layout                          $layout,
        TemplateFileResolver            $templateFileResolver,
        TemplateEngine                  $templateEngine,
        State                           $appState,
        Profiler                        $profiler,
        FileHelper                      $fileHelper,
        LoggerInterface                 $logger,
        private readonly ViewableEntity $viewableEntityHelper,
        string                          $nameInLayout = '',
        array                           $data = []
    )
    {
        parent::__construct($layout, $templateFileResolver, $templateEngine, $appState, $profiler, $fileHelper, $logger, $nameInLayout, $data);
    }

    /**
     * @return 'link'|'button'
     */
    public function getType(): string
    {
        $rawType = $this->getData('type');
        if ($rawType !== null) {
            return $rawType;
        }
        return 'link';
    }

    public function getHref(): string|null
    {

        $page = $this->getData('page');
        if ($page !== null) {
            return $this->viewableEntityHelper->getUrl($page);
        }
        $link = $this->getData('link');
        if ($link !== null) {
            return $link;
        }

        $href = $this->getData('href');
        if ($href !== null) {
            return $href;
        }

        $this->logger->warning('[Link] No `page`, `link` or `href` found', ['data' => $this->getData(), 'n' => $this->getNameInLayout()]);

        return null;
    }

    public function showArrow(): bool
    {
        $arrow = $this->getData('arrow');
        return $arrow === 'true';
    }

    public function getContent(): string
    {
        $content = $this->getData('content');
        if ($content === null || $content === '') {
            $this->logger->warning('[Link] No content defined');
            $content = '[Link]';
        }
        return $content;
    }


}
