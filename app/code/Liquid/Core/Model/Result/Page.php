<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Content\Block\Element\MarkupEngine\CopyBlockTag;
use Liquid\Content\Block\Element\MarkupEngine\LinkTag;
use Liquid\Content\Block\Element\MarkupEngine\PageSectionTag;
use Liquid\Content\Block\HtmlHeadBlock;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Helper\MarkupEngine;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Helper\TerminologyHelper;
use Liquid\Content\Model\MarkupEngine\TestSection;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Layout;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\ApplicationMode;
use Liquid\Core\Model\Request\Response;
use Psr\Log\LoggerInterface;

class Page extends Result
{
    private string $template = 'Liquid_Content::root.phtml';
    private array $viewVars = [];

    public function __construct(
        private readonly Layout            $layout,
        private readonly PageConfig        $pageConfig,
        private readonly AppConfig         $appConfig,
        private readonly TemplateHelper    $templateHelper,
        private readonly Resolver          $resolver,
        private readonly TerminologyHelper $terminologyHelper,
        private readonly LocaleHelper      $localeHelper,
        private readonly MarkupEngine      $markupEngine,
        private readonly Profiler          $profiler,
        private readonly LoggerInterface   $logger,
    ) {
    }


    protected function _render(Response $response): self
    {
        $response->setHeader('Content-Type', 'text/html', true);
        //$response->setHeader('Content-Security-Policy', "default-src 'self'; data:");

        $this->pageConfig->addBodyClass('no-animations');

        $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, PageConfig::HTML_ATTRIBUTE_LANG, $this->appConfig->getLocale()->langCode);
        $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, 'itemscope', '');
        $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, 'itemtype', 'http://schema.org/WebPage');


        $layoutOutput = $this->layout->getOutput();


        $layoutOutput = $this->engageMarkupEngine($layoutOutput);


        /** @var HtmlHeadBlock $headBlock */
        $headBlock = $this->layout->getBlock('head');
        if (\is_null($headBlock)) {
            $this->logger->error('Unable to render headContent: block head not found');
            $this->viewVars['headContent'] = '';
        } else {
            $this->viewVars['headContent'] = $headBlock->toHtml();
        }


        // TODO: how to pass on more attributes if wanted
        $this->viewVars['htmlAttributes'] = $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_HTML);
        $this->viewVars['headAttributes'] = $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_HEAD);
        $this->viewVars['bodyAttributes'] = $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_BODY);
        $this->viewVars['layoutContent'] = $layoutOutput;

        $output = $this->renderPage();


        $output = $this->terminologyHelper->buildTerms($output);


        if ($this->appConfig->debugTranslations()) {
            $this->localeHelper->findMissingTranslations($output);
        }
        if ($this->appConfig->debugTerms()) {
            $this->terminologyHelper->findMissingTerms($output);
        }

        $output = $this->sanitizeHtml($output);

        $content = $response->getContent();
        $response->setContent($content . $output);
        return $this;
    }

    private function sanitizeHtml(string $content): string
    {

        if ($this->appConfig->getMode() === ApplicationMode::PRODUCTION) {
            $search = [
                '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
                '/[^\S ]+\</s',     // strip whitespaces before tags, except space
                '/(\s)+/s',         // shorten multiple whitespace sequences
                '/<!--(.|\s)*?-->/' // Remove HTML comments
            ];
        } else {
            $search = [
//                '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
//                '/[^\S ]+\</s',     // strip whitespaces before tags, except space
// '/(\s)+/s',         // shorten multiple whitespace sequences
//                '/<!--(.|\s)*?-->/' // Remove HTML comments
            ];
        }


        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        return preg_replace($search, $replace, $content);
    }

    private function renderElementAttributes(string $elementType): string
    {
        $resultAttributes = [];
        $elementAttributes = $this->pageConfig->getElementAttributes($elementType);
        foreach ($elementAttributes as $name => $value) {
            $resultAttributes[] = sprintf('%s="%s"', $name, $value);
        }
        return implode(' ', $resultAttributes);
    }

    private function engageMarkupEngine(string $layoutOutput): string
    {
        $this->profiler->profilerStart('MarkupEngine:run');

        // TODO: registration of tags should happen elsewhere
        $this->markupEngine->registerTag('ct:section', TestSection::class);
        $this->markupEngine->registerTag('page-header', TemplateBlock::class, ['template' => 'element/markupengine/page-header.phtml']);
        $this->markupEngine->registerTag('page-section', PageSectionTag::class, ['template' => 'element/markupengine/page-section.phtml']);
        $this->markupEngine->registerTag('link', LinkTag::class);
        $this->markupEngine->registerTag('copy-block', CopyBlockTag::class);

        $layoutOutput = $this->markupEngine->parse($layoutOutput);

        $this->profiler->profilerFinish();

        return $layoutOutput;
    }

    protected function renderPage(): string
    {
        $fileName = $this->templateHelper->getTemplateFileName($this->template);
        if (!$fileName) {
            throw new \InvalidArgumentException('Template "' . $this->template . '" is not found');
        }

        ob_start();
        try {
            extract($this->viewVars, EXTR_SKIP);
            include $fileName;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        return ob_get_clean();
    }


    public function getResolver(): Resolver
    {
        return $this->resolver;
    }
}
