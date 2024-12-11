<?php
declare(strict_types=1);

namespace Liquid\Framework\Component;

class ComponentFile
{
    /**
     * Scope separator for module notation of file ID
     */
    public const string FILE_ID_SEPARATOR = '::';

    public function __construct(
        private readonly ComponentType $componentType,
        private readonly string        $componentId,
        private readonly string        $fullPath
    )
    {

    }

    /**
     * Extract module name from specified file ID
     *
     * @param string $fileId
     * @return array{moduleId: string|null, filePath: string}
     */
    public static function extractModule(string $fileId): array
    {
        if (!$fileId || !str_contains($fileId, self::FILE_ID_SEPARATOR)) {
            return ['moduleId' => null, 'filePath' => $fileId];
        }
        $result = explode(self::FILE_ID_SEPARATOR, $fileId, 2);
        if (empty($result[0])) {
            throw new \RuntimeException('Scope separator "::" cannot be used without scope identifier.');
        }
        return ['moduleId' => $result[0], 'filePath' => $result[1]];
    }

    public function getComponentType(): ComponentType
    {
        return $this->componentType;
    }

    public function getComponentId(): string
    {
        return $this->componentId;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }
}
