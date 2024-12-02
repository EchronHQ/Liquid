<?php
declare(strict_types=1);

namespace Liquid\Framework\Cache;

interface FrontendInterface
{
    /**
     * Test if a cache is available for the given id
     *
     * @param string $identifier Cache id
     * @return int|null Last modified time of cache entry if it is available, false otherwise
     */
    public function test(string $identifier): int|null;

    /**
     * Load cache record by its unique identifier
     *
     * @param string $identifier
     * @return string|null
     */
    public function load(string $identifier): string|null;

    /**
     * Save cache record
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param \DateInterval|null $lifeTime
     * @return bool
     */
    public function save(string $data, string $identifier, array $tags = [], \DateInterval|null $lifeTime = null): bool;

    /**
     * Remove cache record by its unique identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove(string $identifier): bool;

    /**
     * Clean cache records matching specified tags
     *
     * @param CacheCleanMode $mode
     * @param array $tags
     * @return bool
     */
    public function clean(CacheCleanMode $mode = CacheCleanMode::All, array $tags = []): bool;
}
