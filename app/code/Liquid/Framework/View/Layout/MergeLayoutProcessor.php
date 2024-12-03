<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\State;
use Liquid\Framework\Exception\RuntimeException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;
use Liquid\Framework\Simplexml\XmlHelper;
use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\Layout\File\Collector\Aggregated;
use Psr\Log\LoggerInterface;

class MergeLayoutProcessor
{
    /**
     * Cache id suffix for page layout
     */
    public const PAGE_LAYOUT_CACHE_SUFFIX = 'page_layout_merged';
    /**
     * Default cache life time
     */
    private const DEFAULT_CACHE_LIFETIME = 31536000;
    protected string $pageLayout = '';
    /**
     * Status for handle being processed
     *
     * @var int
     */
    protected int $handleProcessing = 1;
    /**
     * Status for processed handle
     *
     * @var int
     */
    protected int $handleProcessed = 2;
    private array $handles = [];

    private int $cacheLifetime;
    private array $allHandles = [];
    private array $updates = [];
    private LayoutActions|null $layoutUpdatesCache = null;

    public function __construct(
        private readonly SerializerInterface                     $serializer,
        private readonly \Liquid\Framework\App\Cache\Type\Layout $cache,
        private readonly State                                   $appState,
        private readonly Theme                                   $theme,
        private readonly Aggregated                              $layoutFileSource,
        private readonly ObjectManagerInterface                  $objectManager,
        private readonly XmlHelper                               $xmlHelper,
        private readonly LoggerInterface                         $logger,
        private readonly Profiler                                $profiler,
        private readonly string                                  $cacheSuffix = '',
        int|null                                                 $cacheLifetime = null

    )
    {
        $this->cacheLifetime = $cacheLifetime ?? self::DEFAULT_CACHE_LIFETIME;
    }

    /**
     * Remove handle from update
     *
     * @param string $handleName
     * @return $this
     */
    public function removeHandle(string $handleName): self
    {
        unset($this->handles[$handleName]);
        return $this;
    }

    /**
     * Load layout updates by handles
     *
     * @param array|string $handles
     * @return $this
     * @throws RuntimeException
     */
    public function load(string|array $handles = []): self
    {
        if (is_string($handles)) {
            $handles = [$handles];
        } elseif (!is_array($handles)) {
            throw new RuntimeException('Invalid layout update handle');
        }

        $this->addHandle($handles);

        $cacheId = $this->getCacheId() . '_' . self::PAGE_LAYOUT_CACHE_SUFFIX;
        $result = $this->_loadCache($cacheId);
        if ($result !== false && $result !== null) {
            $data = $this->serializer->unserialize($result);
            $this->pageLayout = $data["pageLayout"];
            $this->addUpdate($data["layout"]);
            foreach ($this->getHandles() as $handle) {
                $this->allHandles[$handle] = $this->handleProcessed;
            }
            return $this;
        }

        $this->extractHandlers();

        foreach ($this->getHandles() as $handle) {
            $this->_merge($handle);
        }

        $layout = $this->asString();
        $this->_validateMergedLayout($cacheId, $layout);

        $data = [
            "pageLayout" => (string)$this->pageLayout,
            "layout" => $layout,
        ];
        $this->_saveCache($this->serializer->serialize($data), $cacheId, $this->getHandles());

        return $this;
    }

    /**
     * Add handle(s) to update
     *
     * @param string|array $handleName
     * @return $this
     */
    public function addHandle(string|array $handleName): self
    {
        if (is_array($handleName)) {
            foreach ($handleName as $name) {
                $this->handles[$name] = 1;
            }
        } else {
            $this->handles[$handleName] = 1;
        }
        return $this;
    }

    /**
     * Return cache ID based current area/package/theme/store and handles
     *
     * @return string
     */
    public function getCacheId(): string
    {
        $layoutCacheKeys = [];// $this->layoutCacheKey->getCacheKeys();
        return $this->generateCacheId(md5(implode('|', array_merge($this->getHandles(), $layoutCacheKeys))));
    }

    /**
     * Get handle names array
     *
     * @return string[]
     */
    public function getHandles(): array
    {
        return array_keys($this->handles);
    }

    /**
     * Add XML update instruction
     *
     * @param string $update
     * @return $this
     */
    public function addUpdate(string $update): self
    {
        if (!in_array($update, $this->updates, true)) {
            $this->updates[] = $update;
        }
        return $this;
    }

    /**
     * Retrieve already merged layout updates from files for specified area/theme/package/store
     *
     * @return LayoutActions
     * @throws \Exception
     */
    public function getFileLayoutUpdatesXml(): LayoutActions
    {
        if ($this->layoutUpdatesCache) {
            return $this->layoutUpdatesCache;
        }
        $cacheId = $this->generateCacheId($this->cacheSuffix);
        $result = $this->_loadCache($cacheId);
        if ($result) {
            $result = $this->_loadXmlString($result);
        } else {
            $result = $this->_loadFileLayoutUpdatesXml();
            // $this->_saveCache($result->asXML(), $cacheId);
        }
        $this->layoutUpdatesCache = $result;
        return $result;
    }

    public function addUpdate2(array $update): self
    {
        if (!in_array($update, $this->updates, true)) {
            $this->updates[] = $update;
        }
        return $this;
    }

    /**
     * Get all registered updates as string
     *
     * @return string
     */
    public function asString()
    {
        return '';
        return implode('', $this->updates);
    }

    /**
     * Get all registered updates as array
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->updates;
    }

    /**
     * Get layout updates as LayoutElement object
     *
     * @return LayoutElement
     */
    public function asSimplexml(): LayoutElement
    {
        $updates = trim($this->asString());
        $updates = '<?xml version="1.0"?>'
            . '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . $updates
            . '</layout>';
        return $this->_loadXmlString($updates);
    }

    /**
     * Generate cache identifier taking into account current area/package/theme/store
     *
     * @param string $suffix
     * @return string
     */
    protected function generateCacheId(string $suffix = ''): string
    {
        return 'layout_x';
        //return "LAYOUT_{$this->theme->getArea()}_STORE{$this->scope->getId()}_{$this->theme->getId()}{$suffix}";
    }

    /**
     * Retrieve data from the cache, if the layout caching is allowed, or FALSE otherwise
     *
     * @param string $cacheId
     * @return string|bool
     */
    protected function _loadCache(string $cacheId): string|null
    {
        return $this->cache->load($cacheId);
    }

    protected function _loadXmlString(string $xmlString): LayoutElement|null
    {
        $result = simplexml_load_string($xmlString, LayoutElement::class);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * Merge layout update by handle
     *
     * @param string $handle
     * @return $this
     * @throws \Exception
     */
    protected function _merge(string $handle): self
    {
        if (!isset($this->allHandles[$handle])) {
            $this->allHandles[$handle] = $this->handleProcessing;
            $this->_fetchPackageLayoutUpdates($handle);
            // $this->_fetchDbLayoutUpdates($handle);
            $this->allHandles[$handle] = $this->handleProcessed;
        } elseif ($this->allHandles[$handle] == $this->handleProcessing
            && $this->appState->getMode() === AppMode::Develop
        ) {
            $this->logger->info('Cyclic dependency in merged layout for handle: ' . $handle);
        }
        return $this;
    }

    /**
     * Add updates for the specified handle
     *
     * @param string $handle
     * @return bool
     * @throws \Exception
     */
    protected function _fetchPackageLayoutUpdates(string $handle): bool
    {
        $_profilerKey = 'layout_package_update:' . $handle;

        $this->profiler->profilerStart($_profilerKey);
        $layout = $this->getFileLayoutUpdatesXml();

        $handleActions = $layout->get($handle);
        if ($handleActions !== null) {
            //            $this->validateUpdate($handle, $updateInnerXml);
            $this->addUpdate2($handleActions);
        }
//        foreach ($layout->xpath("*[self::handle or self::layout][@id='{$handle}']") as $updateXml) {
//            $this->_fetchRecursiveUpdates($updateXml);
//            $updateInnerXml = $updateXml->innerXml();
//            $this->validateUpdate($handle, $updateInnerXml);
//            $this->addUpdate($updateInnerXml);
//        }
        $this->profiler->profilerFinish($_profilerKey);

        return true;
    }

    /**
     * Collect and merge layout updates from files
     *
     * @return LayoutActions
     * @throws \RuntimeException
     */
    protected function _loadFileLayoutUpdatesXml(): LayoutActions
    {
        $layoutStr = '';

        $files = $this->layoutFileSource->getFiles($this->theme, '*.xml');

        //   var_dump($files);
//        $theme = $this->_getPhysicalTheme($this->theme);
        $updateFiles = [];// $this->fileSource->getFiles($theme, '*.xml');
        $updateFiles = array_merge($updateFiles, $this->layoutFileSource->getFiles($this->theme, '*.php'));
//        $useErrors = libxml_use_internal_errors(true);

        $layoutActions = new LayoutActions();
        foreach ($updateFiles as $file) {
            //    var_dump($file);

            $handleName = basename($file->getFilename(), '.php');
            $actions = require $file->getFilename();

            if (!is_array($actions)) {
                throw new \Exception("File $file should return an array of definitions");
            }
            $layoutActions->add($handleName, $actions);

//            /** @var $fileReader FileRead */
//            $fileReader = $this->objectManager->create(FileRead::class, ['path' => $file->getFilename()]);
//            // $fileReader = $this->readFactory->create($file->getFilename(), DriverPool::FILE);
//            $fileStr = $fileReader->readAll();
//            // $fileStr = $this->_substitutePlaceholders($fileStr);
//
//            $fileXml = $this->_loadXmlString($fileStr);
//            if (!$fileXml instanceof LayoutElement) {
//                $xmlErrors = $this->xmlHelper->getXmlErrors(libxml_get_errors());
//
//                $this->logger->info(
//                    sprintf("Theme layout update file '%s' is not valid.\n%s", $file->getFilename(), implode("\n", $xmlErrors))
//                );
//                // $this->_logXmlErrors($file->getFilename(), $xmlErrors);
//                if ($this->appState->getMode() === AppMode::Develop) {
//                    throw new \RuntimeException("Theme layout update file '" . $file->getFilename() . "' is not valid.\n" . implode("\n", $xmlErrors) . ""
//
//                    );
//                }
////                libxml_clear_errors();
//                continue;
//            }
//            if (!$file->isBase() && $fileXml->xpath('/layout[@design_abstraction]')) {
//                throw new \RuntimeException(
//                    'Theme layout update file \'"' . $file->getFileName() . '"\' must not declare page types.'
//                );
//            }
//            $handleName = basename($file->getFilename(), '.xml');
//            $tagName = $fileXml->getName() === 'layout' ? 'layout' : 'handle';
//            $handleAttributes = ' id="' . $handleName . '"' . $this->xmlHelper->renderXmlAttributes($fileXml);
//            $handleStr = '<' . $tagName . $handleAttributes . '>' . $fileXml->innerXml() . '</' . $tagName . '>';
//            $layoutStr .= $handleStr;
        }
//        libxml_use_internal_errors($useErrors);
//        $layoutStr = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
//        $layoutXml = $this->_loadXmlString($layoutStr);
        return $layoutActions;
    }

    /**
     * Validate merged layout
     *
     * @param string $cacheId
     * @param string $layout
     * @return $this
     * @throws \Exception
     */
    protected function _validateMergedLayout(string $cacheId, string $layout): self
    {
        // $layoutStr = '<handle id="handle">' . $layout . '</handle>';

        try {
            // $this->layoutValidator->isValid($layoutStr, Validator::LAYOUT_SCHEMA_MERGED, false);
        } catch (\Exception $e) {
            // $messages = $this->layoutValidator->getMessages();
            //Add first message to exception
//            $message = reset($messages);
//            $this->logger->info(
//                'Cache file with merged layout: ' . $cacheId
//                . ' and handles ' . implode(', ', (array)$this->getHandles()) . ': ' . $message
//            );
            if ($this->appState->getMode() === AppMode::Develop) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Save data to the cache, if the layout caching is allowed
     *
     * @param string $data
     * @param string $cacheId
     * @param array $cacheTags
     * @return void
     * @throws \Exception
     */
    protected function _saveCache(string $data, string $cacheId, array $cacheTags = []): void
    {
        $this->cache->save($data, $cacheId, $cacheTags, new \DateInterval('PT' . $this->cacheLifetime . 'S'));
    }

    /**
     * Walk all updates and extract handles before the merge step.
     */
    private function extractHandlers(): void
    {
        foreach ($this->updates as $update) {
            $updateXml = null;

            try {
                $updateXml = is_string($update) ? $this->_loadXmlString($update) : false;
            } catch (\Exception $exception) {
                // ignore invalid
            }

            if ($updateXml && strtolower($updateXml->getName()) === 'update' && isset($updateXml['handle'])) {
                $this->addHandle((string)$updateXml['handle']);
            }
        }
    }
}
