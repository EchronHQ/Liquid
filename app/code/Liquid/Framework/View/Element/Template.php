<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Element;

use Liquid\Core\Helper\FileHelper;
use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\State;
use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\View\Element\Template\File\TemplateFileResolver;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\TemplateEngine;
use Psr\Log\LoggerInterface;

/**
 * Standard Liquid block.
 *
 * Should be used when you declare a block in frontend area layout handle.
 *
 * Avoid extending this class.
 *
 * If you need custom presentation logic in your blocks, use this class as block, and declare
 * custom view models in block arguments in layout handle file.
 *
 * Example:
 * <block name="my.block" class="Liquid\Framework\View\Element\Template" template="My_Module::template.phtml" >
 *      <arguments>
 *          <argument name="viewModel" xsi:type="object">My\Module\ViewModel\Custom</argument>
 *      </arguments>
 * </block>
 */
class Template extends AbstractBlock implements BlockInterface
{
    protected string|null $template = null;
    private array $viewVars = [];
    /** @var ArgumentInterface[] */
    private array $viewModel = [];

    public function __construct(
        Layout                                $layout,
        private readonly TemplateFileResolver $templateFileResolver,
        private readonly TemplateEngine       $templateEngine,
        private readonly State                $appState,
        private readonly Profiler             $profiler,
        private readonly FileHelper           $fileHelper,
        protected readonly LoggerInterface    $logger,
        string                                $nameInLayout = '',
        array                                 $data = []
    )
    {
        parent::__construct($layout, $nameInLayout, $data);
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * @template T implement ArgumentInterface
     * @param string $name
     * @param class-string<T>|null $className
     * @return T|null
     */
    public function getViewModel(string $name = '', string|null $className = null): ArgumentInterface|null
    {
        if (!\array_key_exists($name, $this->viewModel)) {
            $this->logger->warning('View model `' . $name . '` not found', [
                'available' => \count($this->viewModel) === 0 ? '<none>' : \array_keys($this->viewModel),
                'block' => $this->getNameInLayout(),
            ]);
            return null;
        }
        $value = $this->viewModel[$name];
        if (($className !== null) && !$value instanceof $className) {
            $this->logger->warning('View model `' . $name . '` expected to be `' . $className . '`, `' . \get_class($value) . '` instead');
        }

        return $value;
    }

    public function setViewModel(ArgumentInterface $viewModel, string $name = ''): self
    {
        if (isset($this->viewModel[$name])) {
            $this->logger->warning('View model `' . $name . '` already set for template `' . $this->getNameInLayout() . '` (old: `' . \get_class($this->viewModel[$name]) . '` new `' . \get_class($viewModel) . '`)');
        }
        $this->viewModel[$name] = $viewModel;
        return $this;
    }

    public function toHtml(): string
    {
        if (empty($this->template)) {
            $this->logger->warning('Template for `' . $this->getNameInLayout() . '` not defined');
            return '';
        }
        $templateFullPath = $this->getTemplateFile();
        if ($templateFullPath === null) {
            // TODO: show warning
            $this->logger->warning('Template for `' . $this->getNameInLayout() . '` not found (' . $this->template . ')', [
                'Module' => $this->getModuleId(),
            ]);
            return '';
        }
        return $this->renderTemplate($templateFullPath);
    }

    /**
     * Get absolute path to template
     *
     * @param string|null $template
     * @return string|null
     * @throws ContextException
     * @throws \JsonException
     */
    public function getTemplateFile(string|null $template = null): string|null
    {
        $params = ['module' => $this->getModuleId()];
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }
        return $this->templateFileResolver->getTemplateFileName($template ?: $this->getTemplate(), $params);
    }

    /**
     * Retrieve module id of block
     * TODO: isn't this obsolete if we don't extend the Template block anymore?
     *
     * @return string|null
     */
    public function getModuleId(): string|null
    {
        if (!$this->_getData('module_id')) {
            $className = \get_class($this);

            $namespace = \substr(
                $className,
                0,
                (int)\strpos($className, '\\' . 'Block' . '\\')
            );
            $moduleId = \str_replace('\\', '_', $namespace);
            // $data = ComponentFile::extractModule(get_class($this));
            $this->setData('module_id', $moduleId);
        }
        return $this->_getData('module_id');
    }

    /**
     * Get design area
     *
     * @return AreaCode
     * @throws ContextException
     */
    public function getArea(): AreaCode
    {
        return $this->_getData('area') ?: $this->appState->getAreaCode();
    }

    public function getTemplate(): string
    {
        if ($this->template !== null) {
            return $this->template;
        }
        if ($this->hasData('template')) {
            $template = $this->getData('template');
            if ($template !== null) {
                return $template;
            }
        }
        $this->logger->warning('[Template Block] No template defined');
        return 'empty.phtml';
    }

    public function setTemplate(string $path): self
    {
        if (empty($path)) {
            throw new \Exception('Template path should not be empty');
        }
        $this->template = $path;
        return $this;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param string $fileName
     * @return string
     * @throws \Throwable
     */
    public function renderTemplate(string $fileName): string
    {
        $this->profiler->profilerStart('TEMPLATE:' . $fileName, ['group' => 'TEMPLATE', 'file_name' => $fileName]);
        if ($this->isValidTemplate($fileName)) {
            $html = $this->templateEngine->render($this, $fileName, $this->viewVars);
        } else {
            $html = '';
            $templatePath = $fileName ?: $this->getTemplate();
            $errorMessage = "Invalid template file: '{$templatePath}' in module: '{$this->getModuleId()}'"
                . " block's name: '{$this->getNameInLayout()}'";

            if ($this->appState->getMode() === AppMode::Develop) {
                throw new \RuntimeException($errorMessage);
            }
            $this->logger->critical($errorMessage);
        }


        $this->profiler->profilerFinish('TEMPLATE:' . $fileName);
        return $html;
    }

    /**
     * Assign variable
     *
     * @param string|array $key
     * @param mixed $value
     * @return  $this
     */
    public function assign(string|array $key, mixed $value = null): self
    {
        if (\is_array($key)) {
            foreach ($key as $subKey => $subValue) {
                $this->assign($subKey, $subValue);
            }
        } else {
            $this->viewVars[$key] = $value;
        }
        return $this;
    }

    protected function isValidTemplate(string $path): bool
    {
        // TODO: replace file helper with more generic class
        return $this->fileHelper->fileExist($path);
    }
}
