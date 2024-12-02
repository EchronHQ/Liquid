<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

class PathMatcher
{
    public static function matches(string $input, string $path): bool
    {
        if ($input === $path) {
            return true;
        }

        $pathSegments = self::explode($path);
        $inputSegments = self::explode($input);

        if (count($pathSegments) !== count($inputSegments)) {
            return false;
        }

        $length = \count($inputSegments);
        for ($i = 0; $i < $length; $i++) {
            $pathSegment = $pathSegments[$i];
            $rewriteInput = $inputSegments[$i];


            if ($pathSegment !== $rewriteInput && ($pathSegment === '' || !str_starts_with($rewriteInput, ':'))) {
                return false;
            }
        }
        return true;
    }

    public static function getMatchValues(string $input, string $path): array|null
    {
        if (!self::matches($input, $path)) {
            return null;
        }

        $pathSegments = self::explode($path);
        $inputSegments = self::explode($input);

        $arguments = [];

        $length = \count($inputSegments);
        for ($i = 0; $i < $length; $i++) {
            $pathSegment = $pathSegments[$i];
            $inputSegment = $inputSegments[$i];
            if (str_starts_with($inputSegment, ':')) {
                $arguments[$inputSegment] = $pathSegment;
            }
        }

        return $arguments;
    }

    private static function explode(string $value): array
    {
        return \explode('/', $value);
    }
}
