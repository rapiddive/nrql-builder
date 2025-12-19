<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Cache;

use Rapiddive\NrqlBuilder\Response\QueryResponse;

/**
 * Interface for caching query results
 */
interface CacheInterface
{
    /**
     * Get cached query response
     *
     * @param string $key Cache key
     * @return QueryResponse|null Cached response or null if not found
     */
    public function get(string $key): ?QueryResponse;

    /**
     * Store query response in cache
     *
     * @param string $key Cache key
     * @param QueryResponse $response Response to cache
     * @param int $ttl Time to live in seconds
     * @return bool True on success
     */
    public function set(string $key, QueryResponse $response, int $ttl): bool;

    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool True if exists
     */
    public function has(string $key): bool;

    /**
     * Delete cached response
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached responses
     *
     * @return bool True on success
     */
    public function clear(): bool;

    /**
     * Get cache statistics
     *
     * @return array Statistics (hits, misses, size, etc.)
     */
    public function getStats(): array;
}

