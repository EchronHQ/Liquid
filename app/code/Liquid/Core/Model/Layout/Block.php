<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Layout;

use Liquid\Content\Block\Html\Picture;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Model\Asset\AssetSizeInstruction;
use Liquid\Core\Helper\DisableMagicMethodsTrait;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Output;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Layout;
use Liquid\Core\Model\AppConfig;
use Liquid\Core\Model\ApplicationMode;
use Liquid\Core\Model\BlockContext;
use Psr\Log\LoggerInterface;

class Block extends AbstractBlock
{
    use DisableMagicMethodsTrait;

    protected AppConfig $configuration;
    protected Layout|null $layout;
    protected Resolver $resolver;
    protected FileHelper $fileHelper;
    protected LocaleHelper $localeHelper;
    protected Output $outputHelper;
    protected LoggerInterface $logger;

    public function __construct(
        BlockContext $context,
    )
    {
        $this->configuration = $context->configuration;
        $this->layout = $context->layout;
        $this->resolver = $context->resolver;
        $this->fileHelper = $context->fileHelper;
        $this->localeHelper = $context->localeHelper;
        $this->outputHelper = $context->outputHelper;
        $this->logger = $context->logger;
    }

    protected function beforeToHtml(): void
    {

    }

    public function toHtml(): string
    {
        $this->beforeToHtml();
        //  $debug = false;
        $output = '';
        foreach ($this->getChildNames() as $childName) {
            $child = $this->getLayout()->getBlock($childName);
            //            if ($debug) {
            //                $output .= '<div style="border:2px dotted red">';
            //            }
            if ($child === null) {
                throw new \Exception('Child with name "' . $childName . '" not found');
            }
            try {
                $output .= $child->toHtml();
            } catch (\Throwable $ex) {
                $output .= $this->handleUnableToRender($ex);
            }

//            if ($debug) {
//                $output .= '</div>';
//            }
        }
        return $output;
    }

    protected function handleUnableToRender(\Throwable $ex): string
    {
        $this->logger->error('Unable to render block', ['name' => $this->getNameInLayout(), 'class' => \get_class($this), 'error' => $ex->getMessage(), 'file' => $ex->getFile() . ':' . $ex->getLine()]);
        if ($this->configuration->getMode() === ApplicationMode::DEVELOP) {
            return '<div style="background:rgb(255 255 255 / 80%);border:2px dashed red;padding: 10px;margin:10px;position: absolute;z-index: 999;border-radius: 6px"><div>Unable to render block "' . $this->getNameInLayout() . '"</div><div>Class: "' . \get_class($this) . '</div><div>Msg: ' . $ex->getMessage() . '</div></div>';
        }

        return '';
    }

    final protected function renderTemplate(string $path): string
    {
        if (!$this->isValidTemplate($path)) {
            throw new \Exception('Invalid template: ' . $path);
        }
        ob_start();
        require $path;
        return ob_get_clean();
    }


    final protected function isValidTemplate(string $path): bool
    {
        return $this->fileHelper->fileExist($path);
    }

    final protected function getFileContent(string $path, bool $allowCache = true): string
    {
        if (!$this->fileHelper->fileExist($path)) {
            $this->logger->error('Unable to get file content, file does not exist', ['path' => $path]);
            //            throw new \Exception('Path does not exist: ' . $path);
            return '';
        }
        return $this->fileHelper->getFileContent($path, $allowCache);
    }

    public function renderSVG(string $path): string
    {
        $fullPath = $this->resolver->getFrontendFilePath($path);
        //TODO: handle if file does not exist
        return $this->getFileContent($fullPath);
    }

    public function renderLazyLoad(string $assetFile, string $alt = '', AssetSizeInstruction|array|null $sizeInstruction = null, bool $lazyLoad = true): string
    {
        if ($assetFile === 'random') {
            $assetFile = 'image/placeholder.jpg';
        }
        $picture = $this->layout->createBlock(Picture::class);
        assert($picture instanceof Picture);
        $picture->setSrc($assetFile, $alt);
        if ($sizeInstruction !== null) {
            if (\is_array($sizeInstruction)) {
                $sizeInstruction = new AssetSizeInstruction($sizeInstruction['width'], $sizeInstruction['height']);
            }
            $picture->setSize($sizeInstruction);
        }
        $picture->lazyLoad = $lazyLoad;
        return $picture->toHtml();
    }

    public function getConfiguration(): AppConfig
    {
        return $this->configuration;
    }

    public function getLayout(): Layout
    {
        return $this->layout;
    }

    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function getChildBlock(string $identifier): AbstractBlock|null
    {
        $name = $this->getLayout()->getChildName($this->getNameInLayout(), $identifier);
        if ($name) {
            return $this->getLayout()->getBlock($name);
        }
        return null;
    }


    public function getChildNames(): array
    {
        return $this->getLayout()->getChildNames($this->getNameInLayout());
    }

    final public function translate(string $input): string
    {
        return $this->localeHelper->translate($input);
    }

    final public function getOutputHelper(): Output
    {
        return $this->outputHelper;
    }

    final public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

}
