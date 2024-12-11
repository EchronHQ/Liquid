<?php

declare(strict_types=1);

namespace Liquid\Framework\View\Result;

use Liquid\Content\Block\Element\DemoCallToActionBlock;
use Liquid\Content\Block\Element\MarkupEngine\CopyBlockTag;
use Liquid\Content\Block\Element\MarkupEngine\LinkTag;
use Liquid\Content\Block\Element\MarkupEngine\PageSectionTag;
use Liquid\Content\Block\Element\MarkupEngine\TabsTag;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Helper\MarkupEngine;
use Liquid\Content\Helper\TerminologyHelper;
use Liquid\Content\Model\MarkupEngine\TestSection;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Psr\Log\LoggerInterface;

/**
 * @deprecated
 * Replace this by LayoutPage
 */
class Page extends LayoutPage
{
    private string $template = 'Liquid_Content::root.phtml';
    private array $viewVars = [];

//    public function __construct(
//        private readonly Layout                 $layout,
//        private readonly Builder                $builder,
//        private readonly PageConfig             $pageConfig,
//        private readonly AppConfig              $appConfig,
//        private readonly Resolver               $resolver,
//        private readonly TerminologyHelper      $terminologyHelper,
//        private readonly LocaleHelper           $localeHelper,
//        private readonly MarkupEngine           $markupEngine,
//        private readonly Profiler               $profiler,
//        private readonly ObjectManagerInterface $objectManager,
//        private readonly LoggerInterface        $logger,
//    )
//    {
//    }
    public function __construct(
        Layout                             $layout,
        ObjectManagerInterface             $objectManager,
        //        private readonly Builder                $builder,
        private readonly PageConfig        $pageConfig,
        private readonly ScopeConfig       $appConfig,
        private readonly Resolver          $resolver,
        private readonly TerminologyHelper $terminologyHelper,
        private readonly LocaleHelper      $localeHelper,
        private readonly MarkupEngine      $markupEngine,
        private readonly Profiler          $profiler,
//        private readonly ObjectManagerInterface $objectManager,
        private readonly LoggerInterface   $logger,
    )
    {
        parent::__construct($layout, $objectManager);
    }

    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    protected function render(HttpResponseInterface $response): self
    {
        // $this->pageConfig->publicBuild();

        $response->setHeader('Content-Type', 'text/html', true);
        //$response->setHeader('Content-Security-Policy', "default-src 'self'; data:");

        $this->pageConfig->addBodyClass('no-animations');

        //TODO: re-enable when locale can be loaded again
        //  $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, PageConfig::HTML_ATTRIBUTE_LANG, $this->appConfig->getLocale()->langCode);
        $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, 'itemscope', '');
        $this->pageConfig->setElementAttribute(PageConfig::ELEMENT_TYPE_HTML, 'itemtype', 'http://schema.org/WebPage');


        $layoutOutput = $this->layout->getOutput();

        $layoutOutput = $this->engageMarkupEngine($layoutOutput);


        /** @var Template $headBlock */
        $headBlock = $this->layout->getBlock('head');
        $headContent = '';
        if (\is_null($headBlock)) {
            $this->logger->error('Unable to render headContent: block head not found');
        } else {
            $headContent = $headBlock->toHtml();
        }


        // TODO: how to pass on more attributes if wanted
        $this->assignViewVar([
            'headContent' => $headContent,
            'htmlAttributes' => $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_HTML),
            'headAttributes' => $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_HEAD),
            'bodyAttributes' => $this->renderElementAttributes(PageConfig::ELEMENT_TYPE_BODY),
            'layoutContent' => $layoutOutput,
        ]);


        $output = $this->renderPage();


        $output = $this->terminologyHelper->buildTerms($output);


//        if ($this->appConfig->debugTranslations()) {
//            $this->localeHelper->findMissingTranslations($output);
//        }
//        if ($this->appConfig->debugTerms()) {
//            $this->terminologyHelper->findMissingTerms($output);
//        }

        $output = $this->sanitizeHtml($output);


        $response->appendBody($output);
        return $this;
    }

    /**
     * Assign view variable
     *
     * @param array|string $key
     * @param mixed|null $value
     * @return $this
     */
    protected function assignViewVar(array|string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $subKey => $subValue) {
                $this->assignViewVar($subKey, $subValue);
            }
        } else {
            $this->viewVars[$key] = $value;
        }
        return $this;
    }

    protected function renderPage(): string
    {
        /** @var Template $pageTemplate */
        $pageTemplate = $this->layout->createBlock(Template::class, '-');
        $pageTemplate->assign($this->viewVars);
        $pageTemplate->setTemplate($this->template);
        return $pageTemplate->toHtml();
    }

    private function engageMarkupEngine(string $layoutOutput): string
    {
        $this->profiler->profilerStart('MarkupEngine:run');

        // TODO: registration of tags should happen elsewhere
        $this->markupEngine->registerTag('ct:section', TestSection::class);
        $this->markupEngine->registerTag('page-header', Template::class, ['template' => 'Liquid_Content::element/markupengine/page-header.phtml']);
        $this->markupEngine->registerTag('page-section', PageSectionTag::class, ['template' => 'Liquid_Content::element/markupengine/page-section.phtml']);
        $this->markupEngine->registerTag('link', LinkTag::class);
        $this->markupEngine->registerTag('copy-block', CopyBlockTag::class);
        $this->markupEngine->registerTag('tabs', TabsTag::class);
        $this->markupEngine->registerTag('call-to-action', Template::class, ['template' => 'Liquid_Content::element/democalltoaction.phtml'], DemoCallToActionBlock::class);
        $this->markupEngine->registerTag('blog-related', \Liquid\MarkupEngine\Model\Tags\RelatedBlogPostTag::class, ['template' => 'Liquid_Blog::related-posts-section.phtml']);

        $layoutOutput = $this->markupEngine->parse($layoutOutput);

        $this->profiler->profilerFinish();

        return $layoutOutput;
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

    private function sanitizeHtml(string $html): string
    {
        if ($this->appConfig->getBoolValue('dev/minifyhtml')) {
            return \Liquid\Framework\Output\Html::minify($html);
        }
        return $html;
//
//        if ($this->appConfig->getMode() === ApplicationMode::PRODUCTION) {
//            $search = [
//                '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
//                '/[^\S ]+\</s',     // strip whitespaces before tags, except space
//                '/(\s)+/s',         // shorten multiple whitespace sequences
//                '/<!--(.|\s)*?-->/' // Remove HTML comments
//            ];
//        } else {
//            $search = [
////                '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
////                '/[^\S ]+\</s',     // strip whitespaces before tags, except space
//// '/(\s)+/s',         // shorten multiple whitespace sequences
////                '/<!--(.|\s)*?-->/' // Remove HTML comments
//            ];
//        }
//
//
//        $replace = [
//            '>',
//            '<',
//            '\\1',
//            ''
//        ];
//
//        return preg_replace($search, $replace, $content);
    }
}
