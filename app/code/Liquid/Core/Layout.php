<?php

declare(strict_types=1);

namespace Liquid\Core;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Liquid\Content\Block\Element\Navigation;
use Liquid\Content\Block\Html\Script;
use Liquid\Content\Block\Html\Stylesheet;
use Liquid\Content\Block\HtmlHeadBlock;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Model\Layout\Structure;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Model\Layout\AbstractBlock;
use Liquid\Core\Model\Layout\Block;
use Psr\Log\LoggerInterface;

class Layout
{
    public const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    public const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    public const CONTAINER_OPT_HTML_ID = 'htmlId';
    public const CONTAINER_OPT_LABEL = 'label';


    private Structure $structure;

    /** @var AbstractBlock[] */
    private array $blocks = [];

    public function __construct(
        private readonly Container       $diContainer,
        private readonly Profiler        $profiler,
        private readonly LoggerInterface $logger
    )
    {
        $this->structure = new Structure();
    }

    /**
     * @template T of AbstractBlock - T
     * @param class-string<T> $className
     * @param string $name
     * @param array $arguments
     * @return T
     */
    public function createBlock(string $className = Block::class, string $name = '', array $arguments = []): AbstractBlock
    {
        $name = $this->structure->createStructuralElement($name, self::TYPE_BLOCK, $className);

        $blockInstance = $this->generateBlock($className, $name, $arguments);
        $blockInstance->setLayout($this);
        return $blockInstance;
    }

    /**
     * @template T of AbstractBlock
     * @param class-string<T> $className
     * @param string $name
     * @param array $arguments
     * @return T
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function generateBlock(string $className, string $name, array $arguments = []): AbstractBlock
    {
        $blockInstance = $this->diContainer->make($className, $arguments);
        if (!\is_subclass_of($blockInstance, AbstractBlock::class)) {
            throw new \Error('Invalid block "' . $className . '", must be instance of AbstractBlock');
        }
        $blockInstance->setNameInLayout($name);
        $blockInstance->addDataValues($arguments['data'] ?? []);

        $this->setBlock($name, $blockInstance);
        return $blockInstance;
    }

    public function setBlock(string $name, AbstractBlock $block): void
    {
        $this->blocks[$name] = $block;
    }

    /** @deprecated */
    public function createTemplateBlock(string|null $template): TemplateBlock
    {

        /** @var TemplateBlock $block */
        $block = $this->createBlock(TemplateBlock::class);
        if (!\is_null($template)) {
            // TODO: what is the point of creating a template block without valid template?
            $block->setTemplate($template);
        }
        return $block;
    }


    public function getOutput(): string
    {
        $this->logger->debug('Layout', $this->structure->exportElements());
        //        return '<pre>' . \json_encode($this->structure->exportElements(), \JSON_PRETTY_PRINT) . '</pre>';
        //return;

        return $this->renderElement('root');
    }

//    private function renderChild(string $childName): string
//    {
//        $output = '';
//        $children = $this->getChildNames($childName);
//
//        foreach ($children as $childName) {
//            if ($this->isContainer($childName)) {
//                $output .= $this->renderContainer($childName);
//            } else {
//                $output .= $this->renderBlock($childName);
//            }
//        }
//        return $output;
//    }

    public function renderElement(string $elementName): string
    {
        $result = '';
        if ($this->isContainer($elementName)) {
            $result .= $this->renderContainer($elementName);
        } else {
            $result .= $this->renderBlock($elementName);
        }
        return $result;
    }

    private function renderBlock(string $blockName): string
    {
        $this->profiler->profilerStart('Layout:renderBlock ' . $blockName);


        $block = $this->getBlock($blockName);
        if ($block === null) {
            throw new \Exception('[Layout] Unable to render block, no block with name "' . $blockName . '" found');
        }
        $html = $block->toHtml();
        if (empty($html)) {
            $this->logger->debug('[Layout] Block "' . $blockName . '" is empty');
        }

        $this->profiler->profilerFinish('Layout:renderBlock ' . $blockName);

        return $html;
    }

    private function renderContainer(string $containerName): string
    {
        $html = '';
        $children = $this->getChildNames($containerName);

        $this->logger->debug('Render container "' . $containerName . '"', ['children' => $children]);

        foreach ($children as $childName) {
            $html .= $this->renderElement($childName);
        }

        if ($html === '' || !$this->structure->getAttribute($containerName, self::CONTAINER_OPT_HTML_TAG)) {
            if (empty($html)) {
                $this->logger->debug('[Layout] Container "' . $containerName . '" is empty');
            }
            return $html;
        }
        $htmlId = $this->structure->getAttribute($containerName, self::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->structure->getAttribute($containerName, self::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="' . $htmlClass . '"';
        }

        $htmlTag = $this->structure->getAttribute($containerName, self::CONTAINER_OPT_HTML_TAG);

        return sprintf('<%1$s%2$s%3$s>%4$s</%1$s>', $htmlTag, $htmlId, $htmlClass, $html);
    }

    /**
     * @template T of AbstractBlock
     * @param AbstractBlock|class-string<T> $block
     * @param string $name
     * @param string $parentName
     * @return T
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function addBlock(AbstractBlock|string $block, string $name = '', string $parentName = ''): AbstractBlock
    {
        if ($block instanceof AbstractBlock) {
            $name = $name ?: $block->getNameInLayout();
        } else {
            $block = $this->generateBlock($block, $name);
        }

        $name = $this->structure->createStructuralElement($name, self::TYPE_BLOCK, $name ?: get_class($block));


        if ($parentName) {
            $this->structure->setAsChild($name, $parentName);
        }


        $block->setNameInLayout($name);
        $this->setBlock($name, $block);
        //        $block->setLayout($this);

        return $block;
    }

    public function setChild(string $parentName, string $elementName, string $alias): void
    {
        $this->structure->setAsChild($elementName, $parentName, $alias);
    }

    public function getChildNames(string $parentName): array
    {
        return array_keys($this->structure->getChildren($parentName));
    }

    public function unsetChild(string $parentName, string $alias): void
    {
        $this->structure->unsetChild($parentName, $alias);
    }

    public function renameElement(string $oldName, string $newName): void
    {
        if (isset($this->blocks[$oldName])) {
            $block = $this->blocks[$oldName];
            $this->blocks[$oldName] = null;
            unset($this->blocks[$oldName]);
            $this->blocks[$newName] = $block;
        }
        $this->structure->renameElement($oldName, $newName);
    }

    public function getChildName(string $parentName, string $name): string|null
    {
        return $this->structure->getChildId($parentName, $name);
    }

    public function getBlock(string $name): AbstractBlock|null
    {
        return $this->blocks[$name] ?? null;
    }

    public function addContainer(string $name, string $label, array $options = [], string $parent = '', string $alias = ''): void
    {
        $name = $this->structure->createStructuralElement($name, self::TYPE_CONTAINER, $alias);
        $options[self::CONTAINER_OPT_LABEL] = $label;


        $this->generateContainer($this->structure, $name, $options);
        if ($parent) {
            $this->structure->setAsChild($name, $parent, $alias);
        }
    }

    private const TYPE_CONTAINER = 'container';
    private const TYPE_BLOCK = 'block';

    public function isContainer(string $name): bool
    {
        if ($this->structure->hasElement($name)) {
            return self::TYPE_CONTAINER === $this->structure->getAttribute($name, 'type');
        }
        return false;
    }

    private function generateContainer(Structure $structure, string $elementName, array $options): void
    {
        unset($options['type']);
        foreach ($options as $key => $value) {
            $structure->setAttribute($elementName, $key, $value);
        }
    }

    public function runHandle(string $handleId): void
    {
        if ($handleId === 'layout-1col') {
            $this->addContainer('root', 'Root');
            //            $this->addContainer('layout-container', 'Layout Container', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'layout-container'], 'root');
            $this->addContainer('layout', 'Layout', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'layout'], 'root');


            /** @var HtmlHeadBlock $headBlock */
            $headBlock = $this->addBlock(HtmlHeadBlock::class, 'head');

            $headBlock->addScript(new Script('js/vendor.js'));
            $headBlock->addScript(new Script('js/main.js'));
            $headBlock->addScript(new Script('js/hero.js'));
            $headBlock->addScript(new Script('js/hero2.js'));
            // Load Sentry over CDN
            //            $sentryCDN = new Script('https://browser.sentry-cdn.com/7.51.0/bundle.tracing.replay.min.js');
            //            $sentryCDN->integrity = 'sha384-DulooquW3C+xZEn0I3jpaZGefuX4TQSKK9QIIODQaijckMg2g8P+n7k4PS7pY75o';
            //            $sentryCDN->crossorigin = 'anonymous';


            //<script
            //  src="https://browser.sentry-cdn.com/7.51.0/bundle.min.js"
            //  integrity="sha384-EOgbBjVzqFplh/e3H3VEqr1AOCKevEtcmi7r3DiQOFYc4iMJCx1/sX/sfka0Woi5"
            //  crossorigin="anonymous"
            //></script>
            //            $sentryCDN->integrity = "sha384-70hBom53vQV6XVoqnEzSlfP8AYzEm6CSuti85YyRLtmm/jbx0GryCQ1z5StcQwsz";
            //$headBlock->addScript($sentryCDN);


            $headBlock->addStyleSheet(new Stylesheet('css/styles.css'));

            /** @var Navigation $siteHeaderBlock */
            $siteHeaderBlock = $this->addBlock(Navigation::class, 'header', 'layout');


            //            $headerBlock->themes = 'theme-dark theme-midnight';

            $siteHeaderBlock->setTemplate('html/site-header.phtml');

            $this->addContainer('global-content', 'Content', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'global-content'], 'layout');


            $this->addContainer('content', 'Content', [], 'global-content');


            $footerBlock = $this->addBlock(TemplateBlock::class, 'footer', 'layout');
            $footerBlock->setTemplate('html/footer.phtml');

            $siteFooterBlock = $this->addBlock(TemplateBlock::class, 'after-footer', 'layout');
            $siteFooterBlock->setTemplate('html/after-footer.phtml');
        } elseif ($handleId === 'layout-2col-left') {
            $this->addContainer('main', 'Main', [self::CONTAINER_OPT_HTML_TAG => 'main'], 'global-content');
            $this->addContainer('section', 'Section', [self::CONTAINER_OPT_HTML_TAG => 'section'], 'main');
            $this->addContainer('columns-container', 'Sidebar container', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'columns-container'], 'section');
            $this->addContainer('column-left', 'Sidebar', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'column-left'], 'columns-container');

            $this->addContainer('column-main', 'Column main', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'column-main'], 'columns-container');
            //            $this->addContainer('content-container', 'Content container', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'content-container'], 'sidebar-container');
            //            $pageWrapper = $this->addBlock(TemplateBlock::class, 'page-wrapper', 'global-content');
            //            $pageWrapper->setTemplate('Documentation::page-layout/2cols-left.phtml');

            $contentName = $this->getChildName('global-content', 'content');
            $this->setChild('column-main', $contentName, 'content');


            $this->addContainer('left', 'Left', [], 'global-content');
        } else {
            throw new \Exception('Unknown handle');
        }
    }
}
