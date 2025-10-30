<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config\Processor;

class PostProcessorComposite implements PostProcessorInterface
{

    /**
     * @param PostProcessorInterface[] $processors
     */
    public function __construct(
        private readonly array $processors = []
    )
    {

    }

    /**
     * @param array $config
     * @return array
     */
    public function process(array $config): array
    {
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }

        return $config;
    }
}
