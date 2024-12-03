<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Layout;

use Liquid\Content\Helper\LocaleHelper;
use Liquid\Core\Helper\DisableMagicMethodsTrait;
use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Output;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\BlockContext;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\View\Element\AbstractBlock;
use Liquid\Framework\View\Layout\Layout;
use Psr\Log\LoggerInterface;

class Block extends AbstractBlock
{
    use DisableMagicMethodsTrait;

    protected SegmentConfig $configuration;
    protected Layout|null $layout;
    protected Resolver $resolver;
    protected FileHelper $fileHelper;
    protected LocaleHelper $localeHelper;
    protected Output $outputHelper;
    protected LoggerInterface $logger;

    public function __construct(
        BlockContext $context,
        string       $nameInLayout = '',
        array        $data = []
    )
    {
        parent::__construct($context->layout, $nameInLayout, $data);

        $this->configuration = $context->configuration;
        $this->layout = $context->layout;
        $this->resolver = $context->resolver;
        $this->fileHelper = $context->fileHelper;
        $this->localeHelper = $context->localeHelper;
        $this->outputHelper = $context->outputHelper;
        $this->logger = $context->logger;
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

    public function getChildNames(): array
    {
        return $this->getLayout()->getChildNames($this->getNameInLayout());
    }

    public function getLayout(): Layout
    {
        return $this->layout;
    }

    public function getConfiguration(): SegmentConfig
    {
        return $this->configuration;
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

    protected function beforeToHtml(): void
    {

    }

    protected function handleUnableToRender(\Throwable $ex): string
    {
        $this->logger->error('Unable to render block', ['name' => $this->getNameInLayout(), 'class' => \get_class($this), 'error' => $ex->getMessage(), 'file' => $ex->getFile() . ':' . $ex->getLine()]);
        if ($this->configuration->getMode() === AppMode::Develop) {
            return '<div style="background:rgb(255 255 255 / 80%);border:2px dashed red;padding: 10px;margin:10px;position: absolute;z-index: 999;border-radius: 6px"><div>Unable to render block "' . $this->getNameInLayout() . '"</div><div>Class: "' . \get_class($this) . '</div><div>Msg: ' . $ex->getMessage() . '</div></div>';
        }

        return '';
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

}
