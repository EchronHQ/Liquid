<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config\Processor;

interface PostProcessorInterface
{
    /**
     * Process config after reading and converting to appropriate format
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array;
}
