<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\Exception\ContextException;

class State
{
    /**
     * Application run code (pass with bootstrap arguments)
     */
    public const PARAM_MODE = 'LQ_MODE';

    private AreaCode|null $areaCode = null;

    public function __construct(
        private readonly ConfigScope $configScope,
        private readonly AppMode     $appMode = AppMode::Production
    )
    {
    }

    /**
     * Return current app mode
     *
     * @return AppMode
     */
    public function getMode(): AppMode
    {
        return $this->appMode;
    }

    /**
     * Get area code
     *
     * @return AreaCode
     * @throws ContextException
     */
    public function getAreaCode(): AreaCode
    {
        if (!isset($this->areaCode)) {
            throw new ContextException('Area code is not set');
        }
        return $this->areaCode;
    }

    /**
     * Set area code
     *
     * @param AreaCode $code
     * @return void
     * @throws ContextException
     */
    public function setAreaCode(AreaCode $code): void
    {

        if (isset($this->areaCode)) {
            throw new ContextException('Area code is already set');
        }
        $this->configScope->setCurrentScope($code);
        $this->areaCode = $code;
    }
}
