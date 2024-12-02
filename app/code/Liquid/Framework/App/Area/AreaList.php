<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Area;


use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class AreaList
{
    /** @var Area[] */
    private array $areaInstances = [];

    private AreaCode|null $defaultAreaCode = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array<AreaCode,array> $areas
     * @param string|null $defaultAreaCode
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array                  $areas = [],
        string|null                             $defaultAreaCode = null
    )
    {
        if ($defaultAreaCode !== null) {
            $this->defaultAreaCode = AreaCode::tryFrom($defaultAreaCode);
        }
    }

    /**
     * Retrieve area code by front name
     *
     * @param string $frontName
     * @return AreaCode|null
     */
    public function getCodeByFrontName(string $frontName): AreaCode|null
    {
        foreach ($this->areas as $areaCode => $areaInfo) {
//            if (!isset($areaInfo['frontName']) && isset($areaInfo['frontNameResolver'])) {
//                $resolver = $this->_resolverFactory->create($areaInfo['frontNameResolver']);
//                $areaInfo['frontName'] = $resolver->getFrontName(true);
//            }
            if (isset($areaInfo['frontName']) && $areaInfo['frontName'] === $frontName) {
                return $areaCode;
            }
        }
        return $this->defaultAreaCode;
    }

    /**
     * Retrieve area codes
     *
     * @return AreaCode[]
     */
    public function getCodes(): array
    {
        return array_keys($this->areas);
    }

    /**
     * Retrieve default area router id
     *
     * @param AreaCode $areaCode
     * @return string|null
     */
    public function getDefaultRouterId(AreaCode $areaCode): string|null
    {
        return $this->areas[$areaCode->value]['router'] ?? null;
    }

    /**
     * Retrieve application area
     *
     * @param AreaCode $code
     * @return  Area
     */
    public function getArea(AreaCode $code): Area
    {
        if (!isset($this->areaInstances[$code->value])) {
            $this->areaInstances[$code->value] = $this->objectManager->create(
                Area::class,
                ['areaCode' => $code]
            );
        }
        return $this->areaInstances[$code->value];
    }
}
