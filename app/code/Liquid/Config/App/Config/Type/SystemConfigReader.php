<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Type;

use Liquid\Content\Model\Config\Processor\Fallback;
use Liquid\Framework\App\Config\ConfigSourceInterface;

class SystemConfigReader
{
    public function __construct(
        private readonly ConfigSourceInterface $source,
        private readonly Fallback              $fallback,
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
        return $this->fallback->process(
        //   $this->preProcessor->process(
            $this->source->get()
        // )
        );
    }
}
