<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache\Type;

use Liquid\Framework\App\Cache\CacheState;
use Liquid\Framework\Cache\CacheCleanMode;
use Liquid\Framework\Cache\Frontend\Types\Bare;
use Liquid\Framework\Cache\FrontendInterface;

/**
 * Proxy that delegates execution to an original cache type instance, if access is allowed at the moment.
 * It's typical for "access proxies" to have a decorator-like implementation, the difference is logical -
 * controlling access rather than attaching additional responsibility to a subject.
 */
class EnabledProxy extends Bare
{
    public function __construct(
        FrontendInterface           $frontend,
        private readonly CacheState $cacheState,
        private readonly string     $identifier)
    {
        parent::__construct($frontend);
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier): int|null
    {
        if (!$this->isEnabled()) {
            return null;
        }
        return parent::test($identifier);
    }

    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return $this->cacheState->isEnabled($this->identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $identifier): string|null
    {
        if (!$this->isEnabled()) {
            return null;
        }
        return parent::load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $data, string $identifier, array $tags = [], \DateInterval|int|null $lifeTime = null): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $identifier): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }
        return parent::remove($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }
        return parent::clean($mode, $tags);
    }
}
