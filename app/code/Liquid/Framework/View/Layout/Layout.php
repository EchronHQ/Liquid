<?php

declare(strict_types=1);

namespace Liquid\Framework\View\Layout;


use Liquid\Content\Block\Html\Script;
use Liquid\Content\Block\Html\Stylesheet;
use Liquid\Content\ViewModel\HtmlHead;
use Liquid\Core\Helper\Profiler;
use Liquid\Core\Model\Layout\Block;
use Liquid\Framework\DataObject;
use Liquid\Framework\Event\Event;
use Liquid\Framework\Event\EventManager;
use Liquid\Framework\Exception\RuntimeException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\Simplexml\XmlConfig;
use Liquid\Framework\View\Element\AbstractBlock;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Data\LayoutDataStructure;
use Liquid\Framework\View\Layout\Generator\GeneratorContextFactory;
use Liquid\Framework\View\Layout\Generator\GeneratorPool;
use Liquid\Framework\View\Layout\Reader\Context;
use Psr\Log\LoggerInterface;

class Layout extends XmlConfig
{
    public const string CONTAINER_OPT_HTML_TAG = 'htmlTag';
    public const string CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    public const string CONTAINER_OPT_HTML_ID = 'htmlId';
    public const string CONTAINER_OPT_LABEL = 'label';
    /**
     * Default cache life time
     */
    private const int DEFAULT_CACHE_LIFETIME = 31536000;

    private const string TYPE_CONTAINER = 'container';
    private const string TYPE_BLOCK = 'block';
    private Builder|null $builder = null;
    /** @var AbstractBlock[] */
    private array $blocks = [];
    private MergeLayoutProcessor|null $processor = null;
    private array $outputElements = [];
    private Context|null $readerContext = null;
    private int $cacheLifetime;

    public function __construct(
        private readonly Profiler                                $profiler,
        private readonly ReaderPool                              $readerPool,
        private readonly GeneratorPool                           $generatorPool,
        private readonly GeneratorContextFactory                 $generatorContextFactory,
        private readonly ObjectManagerInterface                  $objectManager,
        private readonly \Liquid\Framework\App\Cache\Type\Layout $cache,
        private readonly SerializerInterface                     $serializer,
        private readonly LayoutDataStructure                     $structure,
        private readonly EventManager                            $eventManager,
        private readonly LoggerInterface                         $logger,
        int|null                                                 $cacheLifetime = null
    )
    {
        $this->cacheLifetime = $cacheLifetime ?? self::DEFAULT_CACHE_LIFETIME;
    }

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
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

    /** @deprecated */
    public function createTemplateBlock(string|null $template): Template
    {
        $this->build();
        /** @var Template $block */
        $block = $this->createBlock(Template::class);
        if ($template !== null) {
            // TODO: what is the point of creating a template block without valid template?
            $block->setTemplate($template);
        }
        return $block;
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
        $this->build();
        $name = $this->structure->createStructuralElement($name, self::TYPE_BLOCK, $className);

        $blockInstance = $this->generateBlock($className, $name, $arguments);
        $blockInstance->setLayout($this);
        return $blockInstance;
    }

    public function setBlock(string $name, AbstractBlock $block): void
    {
        $this->blocks[$name] = $block;
    }

    public function getOutput(): string
    {
        $this->build();

        $this->logger->debug('Layout::getOutput', $this->structure->exportElements());
        //        return '<pre>' . \json_encode($this->structure->exportElements(), \JSON_PRETTY_PRINT) . '</pre>';
        //return;

        //  var_dump($this->outputElements);
        $output = '';
        foreach ($this->outputElements as $outputElement) {
            $output .= $this->renderElement($outputElement);
        }
        return $output;

    }

    public function renderElement(string $elementName, bool $useCache = true): string
    {
        $this->build();

        $result = '';
        if ($this->isContainer($elementName)) {
            $result .= $this->renderContainer($elementName);
        } else {
            $result .= $this->renderBlock($elementName);
        }

        $this->eventManager->dispatch(
            'core_layout_render_element',
            new Event(['element_name' => $elementName, 'layout' => $this, 'transport' => new DataObject(['output' => $result])])
        );
        return $result;
    }

    public function isContainer(string $name): bool
    {
        $this->build();
        if ($this->structure->hasElement($name)) {
            return self::TYPE_CONTAINER === $this->structure->getAttribute($name, 'type');
        }
        return false;
    }

    public function getChildNames(string $parentName): array
    {
        $this->build();
        return \array_keys($this->structure->getChildren($parentName));
    }

    public function getBlock(string $name): AbstractBlock|null
    {
        $this->build();
        return $this->blocks[$name] ?? null;
    }

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return $this
     */
    public function removeOutputElement(string $name): self
    {
        if (isset($this->outputElements[$name])) {
            unset($this->outputElements[$name]);
        }
        return $this;
    }

    public function unsetChild(string $parentName, string $alias): void
    {
        $this->build();
        $this->structure->unsetChild($parentName, $alias);
    }

    public function renameElement(string $oldName, string $newName): void
    {
        $this->build();
        if (isset($this->blocks[$oldName])) {
            $block = $this->blocks[$oldName];
            $this->blocks[$oldName] = null;
            unset($this->blocks[$oldName]);
            $this->blocks[$newName] = $block;
        }
        $this->structure->renameElement($oldName, $newName);
    }

    /**
     * @param string $handleId
     * @return void
     * @deprecated
     */
    public function runHandle(string $handleId): void
    {
        $this->logger->debug('Run handle ' . $handleId);
        $this->build();
        //var_dump($handleId);
        if ($handleId === 'layout-1col') {
//            $this->addContainer('root', 'Root');
            //            $this->addContainer('layout-container', 'Layout Container', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'layout-container'], 'root');
            $this->addContainer('layout', 'Layout', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'layout'], 'root');


            $headBlock = $this->addBlock(Template::class, 'head');

            $headBlockViewModel = $this->objectManager->create(HtmlHead::class);

            $headBlock->setViewModel($headBlockViewModel)->setTemplate('Liquid_Content::html/head.phtml');
            $headBlockViewModel->addScript(new Script('js/vendor.js'));
            $headBlockViewModel->addScript(new Script('js/main.js'));
            $headBlockViewModel->addScript(new Script('js/hero.js'));
            $headBlockViewModel->addScript(new Script('js/hero2.js'));
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


            $headBlockViewModel->addStyleSheet(new Stylesheet('css/styles.css'));
            $headBlockViewModel->addStyleSheet(new Stylesheet('css/tailwind.css'));

            $navigationViewModel = $this->objectManager->create(\Liquid\Content\ViewModel\Navigation::class);
            $baseViewModel = $this->objectManager->create(\Liquid\Content\ViewModel\BaseViewModel::class);
            $siteHeaderBlock = $this->addBlock(Template::class, 'header', 'layout');
            $siteHeaderBlock->setViewModel($navigationViewModel, 'navigation')
                ->setViewModel($baseViewModel, 'base')
                ->setTemplate('Liquid_Content::html/site-header.phtml');

            //            $headerBlock->themes = 'theme-dark theme-midnight';

            //$siteHeaderBlock->setTemplate('html/site-header.phtml');

            $this->addContainer('global-content', 'Content', [self::CONTAINER_OPT_HTML_TAG => 'div', self::CONTAINER_OPT_HTML_CLASS => 'global-content'], 'layout');


            $this->addContainer('content', 'Content', [], 'global-content');


            $footerBlock = $this->addBlock(Template::class, 'footer', 'layout');
            $footerBlock
                ->setViewModel($baseViewModel)
                ->setTemplate('Liquid_Content::html/footer.phtml');

            $siteFooterBlock = $this->addBlock(Template::class, 'after-footer', 'layout');
            $siteFooterBlock->setTemplate('Liquid_Content::html/after-footer.phtml');
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

    public function addContainer(string $name, string $label, array $options = [], string $parent = '', string $alias = ''): void
    {
        $this->build();

        $name = $this->structure->createStructuralElement($name, self::TYPE_CONTAINER, $alias);
        $options[self::CONTAINER_OPT_LABEL] = $label;


        $this->generateContainer($this->structure, $name, $options);
        if ($parent) {
            $this->structure->setAsChild($name, $parent, $alias);
        }
        $this->logger->debug('Add container ' . $name, $this->structure->exportElements());
    }

    /**
     * @template T of Template
     * @param Template|class-string<T> $block
     * @param string $name
     * @param string $parentName
     * @return T
     * @throws \Exception
     */
    public function addBlock(Template|string $block, string $name = '', string $parentName = ''): Template
    {
        $this->build();

        if ($block instanceof Template) {
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

    public function getChildName(string $parentName, string $name): string|null
    {
        $this->build();
        return $this->structure->getChildId($parentName, $name);
    }

    public function setChild(string $parentName, string $elementName, string $alias = ''): void
    {
        $this->build();
        $this->structure->setAsChild($elementName, $parentName, $alias);
    }

    public function generateElements(): void
    {
        /**
         * TODO:
         * Rewrite this so we don't use XML to build up our layout as it overcomplicates what we want to achieve with Liquid
         * Some layout modification would be nice but we need to investigate later on how to achieve that
         *
         * The structure is a good idea, build the structure before rendering the elements. This allow manipulation of the structure before putting it all together
         */

        $this->profiler->profilerStart(__CLASS__ . '::' . __METHOD__);
        $cacheId = 'structure_' . $this->getProcessor()->getCacheId();
        $result = $this->cache->load($cacheId);
        if ($result) {

            $data = $this->serializer->unserialize($result);
            $this->getReaderContext()->getPageConfigStructure()->populateWithArray($data['pageConfigStructure']);
            $this->getReaderContext()->getScheduledStructure()->populateWithArray($data['scheduledStructure']);
        } else {
            $this->profiler->profilerStart('build_structure');
            $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
            $this->profiler->profilerFinish('build_structure');

            $data = [
                'pageConfigStructure' => $this->getReaderContext()->getPageConfigStructure()->__toArray(),
                'scheduledStructure' => $this->getReaderContext()->getScheduledStructure()->__toArray(),
            ];
            $handles = $this->getProcessor()->getHandles();
            $this->cache->save($this->serializer->serialize($data), $cacheId, $handles, $this->cacheLifetime);
        }

        $generatorContext = $this->generatorContextFactory->create(
            [
                'structure' => $this->structure,
                'layout' => $this,
            ]
        );

        $this->profiler->profilerStart('generate_elements');
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);
        $this->profiler->profilerFinish('generate_elements');

        $this->addToOutputRootContainers();
        $this->profiler->profilerFinish(__CLASS__ . '::' . __METHOD__);
    }

    public function getProcessor(): MergeLayoutProcessor
    {
        if ($this->processor === null) {
            $this->processor = $this->objectManager->create(MergeLayoutProcessor::class);
        }
        return $this->processor;
    }

    /**
     * Getter and lazy loader for reader context
     *
     * @return Context
     */
    public function getReaderContext(): Context
    {
        if (!$this->readerContext) {
            $this->readerContext = $this->objectManager->create(Context::class);
        }
        return $this->readerContext;
    }

    /**
     * Add an element to output
     *
     * @param string $name
     * @return $this
     */
    public function addOutputElement(string $name): self
    {
        $this->outputElements[$name] = $name;
        return $this;
    }

    /**
     * Layout xml generation
     *
     * @return $this
     * @throws RuntimeException
     */
    public function generateXml(): self
    {
        $xml = $this->getProcessor()->asSimplexml();
        $this->setXml($xml);
//        $this->structure->importElements([]);
        return $this;
    }

    protected function build(): void
    {
        if (!empty($this->builder)) {
            $this->builder->build();
        } else {
            throw new \Exception('Layout builder not defined');
            $this->logger->warning('Layout builder not defined');
        }
    }

    /**
     * Add parent containers to output
     *
     * @return $this
     */
    protected function addToOutputRootContainers(): self
    {
//        var_dump('Output');
//        var_dump($this->structure->exportElements());

        $this->logger->debug('Layout::addToOutputRootContainers', $this->structure->exportElements());
        foreach ($this->structure->exportElements() as $name => $element) {
            if ($element['type'] === self::TYPE_CONTAINER && empty($element['parent'])) {
                $this->addOutputElement($name);
            }
        }
        return $this;
    }

    /**
     * @template T of AbstractBlock
     * @param class-string<T> $className
     * @param string $name
     * @param array $arguments
     * @return T
     */
    private function generateBlock(string $className, string $name, array $arguments = []): AbstractBlock
    {
        $blockInstance = $this->objectManager->create($className, $arguments);
        if (!\is_subclass_of($blockInstance, AbstractBlock::class)) {
            throw new \Error('Invalid block "' . $className . '", must be instance of ' . AbstractBlock::class);
        }
        $blockInstance->setNameInLayout($name);
        $blockInstance->addData($arguments['data'] ?? []);

        $this->setBlock($name, $blockInstance);
        return $blockInstance;
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

        return \sprintf('<%1$s%2$s%3$s>%4$s</%1$s>', $htmlTag, $htmlId, $htmlClass, $html);
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

    private function generateContainer(LayoutDataStructure $structure, string $elementName, array $options): void
    {
        unset($options['type']);
        foreach ($options as $key => $value) {
            $structure->setAttribute($elementName, $key, $value);
        }
    }


}
