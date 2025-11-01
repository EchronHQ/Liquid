<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

class ConfigElement
{
    public function __construct(private array $data)
    {

    }

    public function merge(ConfigElement $newData): void
    {
        $this->data = $this->mergeData($this->data, $newData->data);
    }

    private function mergeData(array $existingData, array $newData): array
    {
        foreach ($newData as $key => $value) {
            if (\is_numeric($key)) {
                // TODO: should we allow numeric keys? this might lead to wrong merges
            }
            $existingNode = isset($existingData[$key]) ? $existingData[$key] : null;

            // TODO: make sure the existing and new data have the same node type

            if ($existingNode !== null) {
                $nodeType = $this->getNodeType($existingNode);
                if ($nodeType === 'text') {
                    throw new \Exception('Needs implementation');
                } else if ($nodeType === 'array') {

                    $existingData[$key] = $this->mergeData($existingNode, $value);
//                    foreach ($value as $childKey => $childElement) {
//
//                        $this->merge();
//                       // $this->data[$key][$childKey] = $childElement;
//                    }
                }

            } else {
                $existingData[$key] = $value;
            }
        }
        return $existingData;
    }

    private function getNodeType(mixed $data): string
    {
        if (\is_string($data)) {
            return 'string';
        }
        if (\is_array($data)) {
            return 'array';
        }
        return '';
    }

    public function getData(): array
    {
        return $this->data;
    }
}
