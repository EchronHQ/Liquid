<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

/**
 * Modular Switch
 *
 * Fallback rule that delegates execution to either modular or non-modular sub-rule depending on input parameters.
 */
class ModularSwitch implements ResolveTypeInterface
{
    public function __construct(
        private readonly ResolveTypeInterface $ruleNonModular,
        private readonly ResolveTypeInterface $ruleModular)
    {
    }

    /**
     * Delegate execution to either modular or non-modular sub-rule depending on input parameters
     *
     * @param array $params
     * @return array
     */
    public function getPatternDirs(array $params): array
    {
        if (isset($params['module_name'])) {
            return $this->ruleModular->getPatternDirs($params);
        }

        return $this->ruleNonModular->getPatternDirs($params);
    }
}
