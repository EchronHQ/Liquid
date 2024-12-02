<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;

class ResolveModuleFiles implements ResolveTypeInterface
{
    public function __construct(
        private readonly ResolveTypeInterface        $resolver,
        private readonly ComponentRegistrarInterface $componentRegistrar
    )
    {

    }

    /**
     * Propagate parameters necessary for modular rule basing on module_name parameter
     *
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params): array
    {
        if (!array_key_exists('module_name', $params)) {
            throw new \InvalidArgumentException(
                'Required parameter "module_name" is not specified.'
            );
        }
        $params['module_dir'] = $this->componentRegistrar->getPath(
            ComponentType::Module,
            $params['module_name']
        );
        if (empty($params['module_dir'])) {
            return [];
        }
        return $this->resolver->getPatternDirs($params);
    }
}
