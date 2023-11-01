<?php

declare(strict_types=1);

namespace Liquid\Content\Block;

use Liquid\Content\Block\Html\Script;
use Liquid\Content\Block\Html\Stylesheet;
use Liquid\Content\Helper\FrontendFileHelper;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Locale;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\Repository\LocaleRepository;
use Liquid\Core\Model\ApplicationMode;
use Liquid\Core\Model\BlockContext;
use Liquid\Seo\Helper\LdGenerator;

class HtmlHeadBlock extends TemplateBlock
{
    /** @var Script[] */
    private array $scripts = [];
    /** @var Stylesheet[] */
    private array $styleSheets = [];
    //    private array $icons = [
    //
    //        ['rel' => 'apple-touch-icon', 'sizes' => '72x72'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '144x144'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '152x152'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '192x192'],
    //        ['rel' => 'icon', 'sizes' => '192x192', 'type' => 'image/png'],
    //        ['rel' => 'icon', 'sizes' => '192x192', 'type' => 'image/png'],
    //    ];


    protected string|null $template = 'Liquid_Content::html/head.phtml';

    public function __construct(
        BlockContext                        $context,
        TemplateHelper                      $templateHelper,
        private readonly PageConfig         $pageConfig,
        private readonly FrontendFileHelper $frontendFileHelper,
        private readonly LocaleRepository   $localeRepository
    )
    {
        parent::__construct($context, $templateHelper);
    }

    public function getPageConfig(): PageConfig
    {
        return $this->pageConfig;
    }

    private function getFrontendBuildUrl(string $fileName): string
    {
        return $this->frontendFileHelper->getUrlByFileName($fileName);
    }

    public function getStyleSheetUrl(string $file): string
    {
        return $this->getFrontendBuildUrl($file);
    }

    public function getJavascriptUrl(string $file): string
    {
        return $this->getFrontendBuildUrl($file);
    }

    public function renderCriticalCss(): string
    {
        $filePath = $this->frontendFileHelper->getFilePath('css/critical.css');


        if (\is_null($filePath)) {
            $this->logger->warning('No critical css found');
            return '';
        }

        $criticalCss = $this->getFileContent($filePath);
        // Remove comments
        $criticalCss = str_replace(["/*", "*/"], ["_COMSTART", "COMEND_"], $criticalCss);
        $criticalCss = preg_replace("/_COMSTART.*?COMEND_/s", "", $criticalCss);


        if ($this->configuration->getMode() === ApplicationMode::DEVELOP) {
            $this->logger->debug('Critical CSS ' . strlen($criticalCss));
        }
        return '<style>' . $criticalCss . '</style>';


    }


    public function addScript(Script $script): void
    {
        $this->scripts[] = $script;
    }

    /**
     * @return Script[]
     */
    public function getScripts(): array
    {
        if (isset($_GET['skipjs'])) {
            return [];
        }
        return $this->scripts;
    }

    public function addStyleSheet(Stylesheet $stylesheet): void
    {
        foreach ($this->styleSheets as $existingStyleSheet) {
            if ($stylesheet->getHref() === $existingStyleSheet->getHref()) {
                $this->logger->warning('Duplicate stylesheet with HREF "' . $stylesheet->getHref() . '"');
            }
        }
        $this->styleSheets[] = $stylesheet;
    }

    /**
     * @return Stylesheet[]
     */
    public function getStyleSheets(): array
    {
        if (isset($_GET['skipcss'])) {
            return [];
        }
        return $this->styleSheets;
    }

    public function renderStyleSheet(Stylesheet $stylesheet): string
    {
        // TODO: validate file extension
        $fullHref = $this->frontendFileHelper->getUrlByFileName($stylesheet->getHref());
        $tags = [
            'href="' . $fullHref . '"',
            'rel="preload"',
            'as="style"',
            "onload=\"this.onload=null; this.rel='" . $stylesheet->getRel() . "';this.media='" . $stylesheet->getMedia() . "'; \"",
        ];
        return '<link ' . \implode(' ', $tags) . '><noscript><link rel="stylesheet" href="' . $fullHref . '"></noscript>';
    }

    public function renderScript(Script $script): string
    {
        // TODO: validate file extension
        $fullSrc = $this->frontendFileHelper->getUrlByFileName($script->getSrc());
        $tags = [
            'type="' . $script->getType() . '"',
            'src="' . $fullSrc . '"',
        ];
        if ($script->isAsync()) {
            $tags[] = 'async';
        }
        if ($script->isDefer()) {
            $tags[] = 'defer';
        }
        if ($script->getCrossorigin() !== null) {
            $tags[] = 'crossorigin="' . $script->getCrossorigin() . '"';
        }
        if ($script->getIntegrity() !== null) {
            $tags[] = 'integrity="' . $script->getIntegrity() . '"';
        }
        return '<script ' . \implode(' ', $tags) . '></script>';
    }

    public function getFrontendFileHelper(): FrontendFileHelper
    {
        return $this->frontendFileHelper;
    }


    public function getSeoImage(AssetSizeInstruction|null $size = null): string
    {
        return $this->getResolver()->getAssetUrl($this->pageConfig->getImage(), $size)->url;
    }

    /**
     * @return Locale[]
     */
    public function getLocales(): array
    {
        return $this->localeRepository->getAll();
    }

    public function getDefaultLocale(): Locale
    {
        return $this->localeRepository->getDefault();
    }


    public function getCurrentUrlForLocale(Locale|null $locale): string
    {
        $currentPath = $this->configuration->getValue('current_path');
        return $this->getResolver()->getUrl($currentPath, $locale);
    }

    public function getCurrentLocale(): Locale|null
    {
        if ($this->configuration->isLocaleDefined()) {
            return $this->configuration->getLocale();
        }
        return null;
    }

    final public function getLdData(): array
    {

        $x = new LdGenerator($this->pageConfig, $this->getResolver());
        return $x->getData();
    }
}
