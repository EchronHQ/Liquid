<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache\Type;

use Liquid\Framework\Cache\Frontend\Types\Tagged;

class Block extends Tagged
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'block_html';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'BLOCK_HTML';


    public function __construct(FrontendTypePool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
