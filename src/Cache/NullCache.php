<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Cache;

use Rapiddive\NrqlBuilder\Response\QueryResponse;

/**
 * No-op cache implementation that doesn't cache anything
 */
class NullCache implements CacheInterface
{
    public function get(string $key): ?QueryResponse
    {
        return null;
    }

    public function set(string $key, QueryResponse $response, int $ttl): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }

    public function getStats(): array
    {
        return [
            'hits' => 0,
            'misses' => 0,
            'size' => 0,
            'hit_rate' => 0,
        ];
    }
}

