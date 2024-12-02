<?php
declare(strict_types=1);

namespace Liquid\Framework\Config\Data;

use Liquid\Framework\App\Cache\Type\Config;
use Liquid\Framework\Config\ConfigData;
use Liquid\Framework\Config\Reader\ConfigReaderInterface;
use Liquid\Framework\Serialize\Serializer\SerializerInterface;

class ScopedData extends ConfigData
{
    private array $_loadedScopes = [];
    private array $_scopePriorityScheme = [];

    public function __construct(
        private readonly ConfigReaderInterface $reader,
        private readonly Config                $cache,
        private readonly SerializerInterface   $serializer,
        private readonly string                $cacheId
    )
    {

    }

    /**
     * @inheritdoc
     */
    public function get(string|null $path = null, mixed $default = null): mixed
    {
        $this->_loadScopedData('global');
        return parent::get($path, $default);
    }

    /**
     * Load data for current scope
     *
     * @param string $scope
     * @return void
     */
    protected function _loadScopedData(string $scope): void
    {
        if (false === isset($this->_loadedScopes[$scope])) {
            if (false === in_array($scope, $this->_scopePriorityScheme, true)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false === isset($this->_loadedScopes[$scopeCode])) {
                    if ($scopeCode !== 'primary' && ($data = $this->cache->load($scopeCode . '::' . $this->cacheId))
                    ) {
                        $data = $this->serializer->unserialize($data);
                    } else {
                        $data = $this->reader->read($scopeCode);
                        if ($scopeCode !== 'primary') {
                            $this->cache->save(
                                $this->serializer->serialize($data),
                                $scopeCode . '::' . $this->cacheId
                            );
                        }
                    }
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode === $scope) {
                    break;
                }
            }
        }
    }
}
