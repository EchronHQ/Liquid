<?php
declare(strict_types=1);

namespace Liquid\Framework\View;

use Liquid\Framework\App\Helper\AbstractHelper;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\BlockInterface;

class TemplateEngine
{
    private BlockInterface|null $currentBlock = null;

    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array                  $blockVariables = []
    )
    {

    }

    /**
     * Render output
     *
     * Include the named PHTML template using the given block as the $this
     * reference, though only public methods will be accessible.
     *
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return string
     * @throws \Throwable
     */
    public function render(BlockInterface $block, string $fileName, array $dictionary = []): string
    {
        \ob_start();
        try {
            $tmpBlock = $this->currentBlock;
            $this->currentBlock = $block;
            $dictionary = \array_merge($this->blockVariables, $dictionary);
            \extract($dictionary, EXTR_SKIP);
            include $fileName;
            $this->currentBlock = $tmpBlock;
        } catch (\Throwable $exception) {
            \ob_end_clean();
            throw $exception;
        }
        return \ob_get_clean();
    }

    /**
     * Get helper singleton
     *
     * @template T extends AbstractHelper
     *
     * @param class-string<T> $className
     * @return T
     * @throws \LogicException
     */
    public function helper(string $className): AbstractHelper
    {
        $helper = $this->objectManager->get($className);
        if (false === $helper instanceof AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends ' . AbstractHelper::class);
        }

        return $helper;
    }
}
