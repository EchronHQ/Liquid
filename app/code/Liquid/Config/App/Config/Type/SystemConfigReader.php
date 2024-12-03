<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Type;

use Liquid\Framework\App\Config\ConfigSourceInterface;

class SystemConfigReader
{
    public function __construct(
        private readonly ConfigSourceInterface $source,
    )
    {

    }

    /**
     * Retrieve and process system configuration data
     *
     * Processing includes configuration fallback (default, website, store) and placeholder replacement
     *
     * @return array
     */
    public function read(): array
    {
        //return $this->fallback->process(
        //   $this->preProcessor->process(
        return $this->source->get();
        // )
        //);
    }
}
