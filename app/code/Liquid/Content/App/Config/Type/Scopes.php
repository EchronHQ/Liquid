<?php
declare(strict_types=1);

namespace Liquid\Content\App\Config\Type;

use Liquid\Content\Model\ScopeType;
use Liquid\Framework\App\Config\ConfigSourceInterface;
use Liquid\Framework\App\Config\ConfigTypeInterface;
use Liquid\Framework\DataObject;

class Scopes implements ConfigTypeInterface
{
    private DataObject|null $data = null;

    /**
     * The field names holder of scope id for specific scope pool.
     * Used for map id to code, e.g. websites/0 to websites/admin
     */
    private array $scopeIdField = [
        ScopeType::WEBSITE->value => 'website_id',
        ScopeType::SEGMENT->value => 'segment_id',
    ];
    /**
     * Map between scope id and scope code
     */
    private array $idCodeMap = [];

    public function __construct(
        private readonly ConfigSourceInterface $source
    )
    {
    }

    public function get(string $path = ''): array|int|string|bool|null
    {
        if (null === $this->data) {
            $this->data = new DataObject($this->source->get());
        }

        $patchChunks = explode('/', (string)$path);
        if (isset($patchChunks[1])
            && is_numeric($patchChunks[1])
            && in_array($patchChunks[0], [ScopeType::WEBSITE, ScopeType::SEGMENT], true)
        ) {
            $path = $this->convertIdPathToCodePath($patchChunks);
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache
     */
    public function clean(): void
    {
        $this->data = null;
        $this->idCodeMap = [];
    }

    /**
     * Replace scope id with scope code. E.g. path 'websites/admin' will be converted to 'websites/0'
     *
     * @param array $patchChunks
     * @return string
     */
    private function convertIdPathToCodePath(array $patchChunks): string
    {
        [$scopePool, $scopeId] = $patchChunks;
        if (!isset($this->idCodeMap[$scopePool]) || !array_key_exists($scopeId, $this->idCodeMap[$scopePool])) {
            $scopeData = $this->data->getData($scopePool);
            foreach ((array)$scopeData as $scopeEntity) {
                if (!isset($scopeEntity[$this->scopeIdField[$scopePool]])) {
                    continue;
                }
                $this->idCodeMap[$scopePool][$scopeEntity[$this->scopeIdField[$scopePool]]] = $scopeEntity['code'];
            }

            if (!isset($this->idCodeMap[$scopePool][$scopeId])) {
                $this->idCodeMap[$scopePool][$scopeId] = null;
            }
        }

        if ($this->idCodeMap[$scopePool][$scopeId]) {
            $patchChunks[1] = $this->idCodeMap[$scopePool][$scopeId];
        }

        return implode('/', $patchChunks);
    }
}
