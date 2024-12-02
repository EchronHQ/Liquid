<?php

declare(strict_types=1);

namespace Liquid\Content\ViewModel;

use Liquid\Content\Block\Html\Script;
use Liquid\Content\Block\Html\Stylesheet;
use Liquid\Content\Helper\FrontendFileHelper;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Content\Model\Segment\Segment;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Config\AppConfig;
use Liquid\Framework\App\State;
use Liquid\Framework\Output\Html;
use Liquid\Framework\Url;
use Liquid\Framework\View\Element\ArgumentInterface;
use Liquid\Seo\Helper\LdGenerator;
use Psr\Log\LoggerInterface;

class HtmlHead implements ArgumentInterface
{
    protected string|null $template = 'Liquid_Content::html/head.phtml';
    /** @var Script[] */
    private array $scripts = [];
    //    private array $icons = [
    //
    //        ['rel' => 'apple-touch-icon', 'sizes' => '72x72'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '144x144'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '152x152'],
    //        ['rel' => 'apple-touch-icon', 'sizes' => '192x192'],
    //        ['rel' => 'icon', 'sizes' => '192x192', 'type' => 'image/png'],
    //        ['rel' => 'icon', 'sizes' => '192x192', 'type' => 'image/png'],
    //    ];
    /** @var Stylesheet[] */
    private array $styleSheets = [];

    public function __construct(
        private readonly PageConfig         $pageConfig,
        private readonly FrontendFileHelper $frontendFileHelper,
        private readonly Resolver           $resolver,
        private readonly SegmentManager     $segmentManager,
        private readonly Url                $url,
        private readonly AppConfig          $config,
        private readonly State              $appState,
        private readonly LoggerInterface    $logger
    )
    {

    }

    public function getPageConfig(): PageConfig
    {
        return $this->pageConfig;
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

        if ($filePath === null) {
            $this->logger->warning('No critical css found');
            return '';
        }

        $inDevMode = true;
        // TODO: this need to be done somewhere else
        $criticalCss = file_get_contents($filePath);// $this->getFileContent($filePath, !$inDevMode);
        if (!$criticalCss) {
            $this->logger->error('Unable to get Critical CSS from file');
            return '';
        }
        if ($this->config->getBool('dev.minifycss', true)) {
            $criticalCss = Html::minifyCss($criticalCss);
        }

        // Remove comments
//        $criticalCss = str_replace(["/*", "*/"], ["_COMSTART", "COMEND_"], $criticalCss);
//        $criticalCss = preg_replace("/_COMSTART.*?COMEND_/s", "", $criticalCss);


        if ($this->appState->getMode() === AppMode::Develop) {
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
        return $this->resolver->getAssetUrl($this->pageConfig->getImage(), $size)->url;
    }

    public function getAssetUrl(string $assetUrl): string
    {
        return $this->resolver->getAssetUrl($assetUrl)->url;
    }

    /**
     * @return Segment[]
     */
    public function getAlternativeSegments(): array
    {
        $result = [];
        $segments = $this->segmentManager->getAll();
        $currentSegment = $this->getCurrentSegment();
        foreach ($segments as $segment) {
            if (!$segment->getId()->equals($currentSegment->getId())) {
                $result[] = $segment;
            }
        }
        return $result;
    }

    public function getCurrentSegment(): Segment|null
    {
        return $this->segmentManager->getSegment();
        // TODO: re-implement locales
//        if ($this->config->isLocaleDefined()) {
//            return $this->config->getLocale();
//        }
        return null;
    }

    public function getDefaultLocale(): Segment
    {
        // TODO: get this from config
        return $this->segmentManager->getSegment(new SegmentId('0'));
    }

    public function getCurrentUrlForSegment(Segment|null $segment): string
    {
        $this->segmentManager->getSegment()->getBaseUrl();
        $currentPath = $this->url->getCurrentUrl();

        return $this->url->getUrl($currentPath, $segment->getId());
    }

    public function getCurrentUrl(): string
    {
        // TODO: implement this
        return $this->config->get('current_url', '');
    }

    final public function getLdData(): array
    {

        return (new LdGenerator($this->pageConfig, $this->resolver))->getData();
    }

    public function getSiteUrl(): string
    {
        return $this->segmentManager->getSegment()->getBaseUrl();
    }

    public function getGoogleTagManagerCode(): string
    {
        if (!$this->isGoogleTagManagerEnabled()) {
            return '';
        }
        return $this->config->get('seo.googletagmanager.code', '');
    }

    public function isGoogleTagManagerEnabled(): bool
    {
        return $this->config->getBool('seo.googletagmanager.enabled', false);
    }

    private function getFrontendBuildUrl(string $fileName): string
    {
        return $this->frontendFileHelper->getUrlByFileName($fileName);
    }
}
