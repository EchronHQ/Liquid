<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

/**
 * Class with simple substitution parameters to values
 */
class ResolveBasicFiles implements ResolveTypeInterface
{
    public function __construct(
        private readonly string $pattern,
        private readonly array  $optionalParams = []
    )
    {

    }

    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params array of parameters
     * @return array folders to perform a search
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params): array
    {
        $pattern = $this->pattern ?? '';
        if (preg_match_all('/<([a-zA-Z\_]+)>/', $pattern, $matches)) {
            foreach ($matches[1] as $placeholder) {
                if (empty($params[$placeholder])) {
                    if (in_array($placeholder, $this->optionalParams)) {
                        return [];
                    } else {
                        throw new \InvalidArgumentException("Required parameter '{$placeholder}' was not passed");
                    }
                }
                $x = $params[$placeholder];
                if ($x instanceof \BackedEnum) {
                    $x = (string)$x->value;
                }
                $pattern = str_replace('<' . $placeholder . '>', $x, $pattern);
            }
        }
        return [$pattern];
    }
}
