<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Client;

use Rapiddive\NrqlBuilder\Cache\CacheInterface;
use Rapiddive\NrqlBuilder\Cache\NullCache;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Response\QueryResponse;
use Rapiddive\NrqlBuilder\Retry\NoRetry;
use Rapiddive\NrqlBuilder\Retry\RetryStrategyInterface;
use RuntimeException;

/**
 * Client for executing NRQL queries via New Relic Insights Query API (REST)
 */
class NewRelicClient implements ClientInterface
{
    private Configuration $config;
    private int $timeout;
    private RetryStrategyInterface $retryStrategy;
    private CacheInterface $cache;
    private int $cacheTtl = 300; // 5 minutes default

    public function __construct(
        Configuration $config,
        int $timeout = 30,
        ?RetryStrategyInterface $retryStrategy = null,
        ?CacheInterface $cache = null
    ) {
        $this->config = $config;
        $this->timeout = $timeout;
        $this->retryStrategy = $retryStrategy ?? new NoRetry();
        $this->cache = $cache ?? new NullCache();
    }

    /**
     * Execute a NRQL query via the Insights Query API
     *
     * @throws RuntimeException When the API request fails
     */
    public function query(QueryBuilder|string $query): QueryResponse
    {
        $nrql = $query instanceof QueryBuilder ? $query->renderNrql() : $query;

        // Check cache first
        $cacheKey = $this->generateCacheKey($nrql);
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $url = $this->config->getInsightsQueryUrl() . '?nrql=' . urlencode($nrql);

        // Execute with retry logic
        $response = $this->executeWithRetry(function() use ($url) {
            return $this->makeRequest($url);
        });

        $queryResponse = $this->parseResponse($response);

        // Cache the result
        $this->cache->set($cacheKey, $queryResponse, $this->cacheTtl);

        return $queryResponse;
    }

    /**
     * Execute a callable with retry logic
     *
     * @param callable $callable The function to execute
     * @return mixed The result
     * @throws RuntimeException When all retry attempts fail
     */
    protected function executeWithRetry(callable $callable): mixed
    {
        $attempt = 1;
        $lastException = null;

        while ($attempt <= $this->retryStrategy->getMaxAttempts()) {
            try {
                return $callable();
            } catch (\Exception $e) {
                $lastException = $e;

                if (!$this->retryStrategy->shouldRetry($attempt, $e)) {
                    throw $e;
                }

                // Calculate and apply delay
                $delay = $this->retryStrategy->getDelay($attempt);
                if ($delay > 0) {
                    usleep($delay * 1000); // Convert ms to microseconds
                }

                $attempt++;
            }
        }

        throw $lastException;
    }

    /**
     * Generate cache key for a query
     */
    protected function generateCacheKey(string $nrql): string
    {
        return 'nrql:' . md5($this->config->getAccountId() . ':' . $nrql);
    }

    /**
     * Make HTTP request to New Relic API
     *
     * @throws RuntimeException When the request fails
     */
    protected function makeRequest(string $url): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-Query-Key: ' . $this->config->getApiKey(),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: $error");
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMessage = $data['error'] ?? 'Unknown error';
            throw new RuntimeException("New Relic API error (HTTP $httpCode): $errorMessage");
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to parse JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Parse API response into QueryResponse object
     */
    protected function parseResponse(array $data): QueryResponse
    {
        $results = $data['results'] ?? [];
        
        $metadata = [
            'performanceStats' => $data['performanceStats'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'totalCount' => count($results),
            'success' => true,
        ];

        return new QueryResponse($results, $metadata);
    }

    /**
     * Get the configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Set request timeout in seconds
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set retry strategy
     */
    public function setRetryStrategy(RetryStrategyInterface $strategy): self
    {
        $this->retryStrategy = $strategy;
        return $this;
    }

    /**
     * Get retry strategy
     */
    public function getRetryStrategy(): RetryStrategyInterface
    {
        return $this->retryStrategy;
    }

    /**
     * Set cache implementation
     *
     * @param CacheInterface $cache Cache implementation
     * @param int $ttl Time to live in seconds (default: 300)
     */
    public function setCache(CacheInterface $cache, int $ttl = 300): self
    {
        $this->cache = $cache;
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * Get cache implementation
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return $this->cache->getStats();
    }

    /**
     * Clear query cache
     */
    public function clearCache(): bool
    {
        return $this->cache->clear();
    }
}

