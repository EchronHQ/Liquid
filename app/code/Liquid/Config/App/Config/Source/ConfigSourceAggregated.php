<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Source;


use Liquid\Framework\App\Config\ConfigSourceInterface;

class ConfigSourceAggregated implements ConfigSourceInterface
{

    /**
     * @var array
     */
    private array $data = [];
    private array $excludedFields = [];

    public function __construct(
        private array $sources = [],

    )
    {
        /* Sort sources ASC from higher priority to lower */
        \uasort($this->sources, static function ($firstItem, $secondItem) {
            return ($firstItem['sortOrder'] <=> $secondItem['sortOrder']);
        });
    }

    /**
     * Retrieve aggregated configuration from all available sources.
     *
     * @param string $path
     * @return array
     */
    public function get(string $path = ''): array
    {
        $data = [];

        if (isset($this->data[$path])) {
            return $this->data[$path];
        }

        foreach ($this->sources as $key => $sourceConfig) {
            /** @var ConfigSourceInterface $source */
            $source = $sourceConfig['source'];

            $data = \array_replace_recursive($data, $source->get($path));
        }
        $this->excludedFields = [];
        $this->filterChain($path, $data);

        return $this->data[$path] = $data;
    }

    /**
     * Recursive filtering of sensitive data
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    private function filterChain(string $path, array &$data): void
    {
        foreach ($data as $subKey => &$subData) {
//            $newPath = $path ? $path . '/' . $subKey : $subKey;
//            $filteredPath = $this->filterPath($newPath);
//
//            if (is_array($subData)) {
//                $this->filterChain($newPath, $subData);
//            } elseif ($this->isExcludedPath($filteredPath)) {
//                $this->excludedFields[$newPath] = $filteredPath;
//                unset($data[$subKey]);
//            }
//
//            if (empty($subData) && isset($data[$subKey]) && is_array($data[$subKey])) {
//                unset($data[$subKey]);
//            }
        }
    }


}
