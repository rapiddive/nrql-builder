<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Response;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Response wrapper for New Relic query results
 */
class QueryResponse implements Iterator, Countable, ArrayAccess
{
    private array $results;
    private array $metadata;
    private int $position = 0;

    public function __construct(array $results, array $metadata = [])
    {
        $this->results = $results;
        $this->metadata = $metadata;
    }

    /**
     * Get all results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get query metadata (performance info, facets, etc.)
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a specific metadata value
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Get total count of results
     */
    public function getTotalCount(): int
    {
        return $this->metadata['totalCount'] ?? count($this->results);
    }

    /**
     * Get performance stats if available
     */
    public function getPerformanceStats(): ?array
    {
        return $this->metadata['performanceStats'] ?? null;
    }

    /**
     * Check if the query was successful
     */
    public function isSuccess(): bool
    {
        return !empty($this->results) || isset($this->metadata['success']);
    }

    // Iterator implementation
    public function current(): mixed
    {
        return $this->results[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->results[$this->position]);
    }

    // Countable implementation
    public function count(): int
    {
        return count($this->results);
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->results[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->results[] = $value;
        } else {
            $this->results[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->results[$offset]);
    }

    /**
     * Convert response to JSON
     */
    public function toJson(): string
    {
        return json_encode([
            'results' => $this->results,
            'metadata' => $this->metadata,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Convert response to array
     */
    public function toArray(): array
    {
        return [
            'results' => $this->results,
            'metadata' => $this->metadata,
        ];
    }
}

