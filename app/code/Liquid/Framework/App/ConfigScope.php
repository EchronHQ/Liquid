<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Area\AreaList;

class ConfigScope
{
    /**
     * Current config scope
     *
     * @var AreaCode
     */
    private AreaCode $currentScope;


    /**
     * Constructor
     *
     * @param AreaList $areaList
     * @param AreaCode $defaultScope
     */
    public function __construct(
        protected readonly AreaList $areaList,
        AreaCode                    $defaultScope = AreaCode::Frontend
    )
    {
        $this->currentScope = $defaultScope;
    }

    /**
     * Get current configuration scope identifier
     *
     * @return AreaCode
     */
    public function getCurrentScope(): AreaCode
    {
        return $this->currentScope;
    }

    /**
     * Set current configuration scope
     *
     * @param AreaCode $scope
     * @return void
     */
    public function setCurrentScope(AreaCode $scope): void
    {
        $this->currentScope = $scope;
    }

    /**
     * Retrieve list of available config scopes
     *
     * @return string[]
     */
    public function getAllScopes(): array
    {
        $codes = $this->areaList->getCodes();
        $codes = \array_map(static function (AreaCode $code) {
            return $code->value;
        }, $codes);
        \array_unshift($codes, 'global', 'primary');

        return $codes;
    }
}
