<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache\Frontend\Types;

use Liquid\Framework\Cache\CacheCleanMode;
use Liquid\Framework\Cache\FrontendInterface;

/**
 * Cache frontend decorator that limits the cleaning scope within a tag
 */
class Tagged extends Bare
{


    public function __construct(FrontendInterface $frontend, private string $cacheTag)
    {
        parent::__construct($frontend);
    }


    public function save(string $data, string $identifier, array $tags = [], \DateInterval|int|null $lifeTime = null): bool
    {
        $tags[] = $this->cacheTag;
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool
    {
        if ($mode === CacheCleanMode::MatchingAnyTag) {
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(CacheCleanMode::MatchingTag, [$tag, $this->cacheTag])) {
                    $result = true;
                }
            }
            return $result;
        }
        if ($mode === CacheCleanMode::All) {
            $mode = CacheCleanMode::MatchingAnyTag;
            $tags = [$this->cacheTag];
        } else {
            $tags[] = $this->cacheTag;
        }
        return parent::clean($mode, $tags);
    }
}
