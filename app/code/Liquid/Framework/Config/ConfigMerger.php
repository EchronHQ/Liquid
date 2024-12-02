<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

/**
 * TODO: write unit tests for the merge method
 */
class ConfigMerger
{
    private ConfigElement $data;

    public function __construct(array $initialData)
    {
        $this->data = new ConfigElement($initialData);
    }

    public function merge(array $data): void
    {

        $this->data->merge(new ConfigElement($data));

        // $this->data = array_merge_recursive($this->data, $data);
    }


    public function getData(): array
    {
        return $this->data->getData();
    }
}
