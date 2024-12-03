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
     * @param array<string,array> $areas
     * @param AreaCode|null $defaultAreaCode
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array                  $areas = [],
        AreaCode|null                           $defaultAreaCode = null
    )
    {
        if ($defaultAreaCode !== null) {
            $this->defaultAreaCode = $defaultAreaCode;
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
            if (!isset($areaInfo['frontName']) && isset($areaInfo['frontNameResolver'])) {
                /** @var FrontNameResolverInterface $resolver */
                $resolver = $this->objectManager->create($areaInfo['frontNameResolver']);
                $areaInfo['frontName'] = $resolver->getFrontName(true);
            }
            if (isset($areaInfo['frontName']) && $areaInfo['frontName'] === $frontName) {
                return $areaInfo['code'];
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
        return array_map(function ($area) {
            return $area['code'];
        }, $this->areas);
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
