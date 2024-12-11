<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Cache\Type;

use Liquid\Framework\Cache\Frontend\Types\Tagged;

class Config extends Tagged
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'config';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'CONFIG';


    public function __construct(FrontendTypePool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}