<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

class Composite implements ResolveTypeInterface
{
    /**
     * Constructors
     *
     * @param ResolveTypeInterface[] $rules
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly array $rules
    )
    {
        foreach ($rules as $rule) {
            if (!$rule instanceof ResolveTypeInterface) {
                throw new \InvalidArgumentException('Each item should implement the ' . ResolveTypeInterface::class . ' interface.');
            }
        }
    }

    /**
     * Retrieve sequentially combined directory patterns from child fallback rules
     *
     * @param array $params
     * @return array
     */
    public function getPatternDirs(array $params): array
    {
        $result = [];
        foreach ($this->rules as $rule) {
            $result = \array_merge($result, $rule->getPatternDirs($params));
        }
        return $result;
    }
}
