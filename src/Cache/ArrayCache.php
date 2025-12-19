<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Cache;

use Rapiddive\NrqlBuilder\Response\QueryResponse;

/**
 * Simple in-memory array cache implementation
 * Useful for testing and single-request caching
 */
class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private array $expiry = [];
    private int $hits = 0;
    private int $misses = 0;

    public function get(string $key): ?QueryResponse
    {
        // Check if key exists and hasn't expired
        if (!isset($this->cache[$key])) {
            $this->misses++;
            return null;
        }

        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            // Expired
            unset($this->cache[$key], $this->expiry[$key]);
            $this->misses++;
            return null;
        }

        $this->hits++;
        return $this->cache[$key];
    }

    public function set(string $key, QueryResponse $response, int $ttl): bool
    {
        $this->cache[$key] = $response;
        $this->expiry[$key] = time() + $ttl;
        return true;
    }

    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            unset($this->cache[$key], $this->expiry[$key]);
            return false;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiry[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expiry = [];
        return true;
    }

    public function getStats(): array
    {
        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'size' => count($this->cache),
            'hit_rate' => $this->hits + $this->misses > 0 
                ? round($this->hits / ($this->hits + $this->misses) * 100, 2) 
                : 0,
        ];
    }
}

