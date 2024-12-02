<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache\Frontend\Types;

use Liquid\Framework\Cache\CacheCleanMode;
use Liquid\Framework\Cache\FrontendInterface;

class Bare implements FrontendInterface
{


    public function __construct(private FrontendInterface $frontend)
    {
    }

    public function test(string $identifier): int|null
    {
        return $this->frontend->test($identifier);
    }

    public function load(string $identifier): string|null
    {
        return $this->frontend->load($identifier);
    }

    public function save(string $data, string $identifier, array $tags = [], \DateInterval|int|null $lifeTime = null): bool
    {
        return $this->frontend->save($data, $identifier, $tags, $lifeTime);
    }

    public function remove(string $identifier): bool
    {
        return $this->frontend->remove($identifier);
    }

    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool
    {
        return $this->frontend->clean($mode, $tags);
    }
}
