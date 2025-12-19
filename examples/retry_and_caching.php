<?php
/**
 * Example: Using Retry Logic and Caching
 * 
 * This example demonstrates the high-priority features:
 * - Exponential backoff retry strategy
 * - Query result caching
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rapiddive\NrqlBuilder\Cache\ArrayCache;
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Retry\ExponentialBackoff;
use Rapiddive\NrqlBuilder\TimePeriod;

echo "=== Retry Logic and Caching Examples ===\n\n";

// Configuration
$apiKey = getenv('NEW_RELIC_API_KEY') ?: 'YOUR_API_KEY_HERE';
$accountId = getenv('NEW_RELIC_ACCOUNT_ID') ?: 'YOUR_ACCOUNT_ID_HERE';

try {
    $config = new Configuration($apiKey, $accountId);
    
    // ========================================
    // Example 1: Basic Retry Strategy
    // ========================================
    echo "Example 1: Client with Retry Strategy\n";
    echo str_repeat('-', 50) . "\n";
    
    // Create retry strategy with exponential backoff
    $retryStrategy = new ExponentialBackoff(
        maxAttempts: 3,      // Retry up to 3 times
        baseDelay: 100,      // Start with 100ms delay
        maxDelay: 5000,      // Max 5 second delay
        useJitter: true      // Add random jitter to prevent thundering herd
    );
    
    $client = new NewRelicClient($config);
    $client->setRetryStrategy($retryStrategy);
    
    echo "✓ Retry strategy configured:\n";
    echo "  - Max attempts: 3\n";
    echo "  - Base delay: 100ms\n";
    echo "  - Max delay: 5000ms\n";
    echo "  - Jitter: enabled\n\n";
    
    // ========================================
    // Example 2: Client with Caching
    // ========================================
    echo "Example 2: Client with Caching\n";
    echo str_repeat('-', 50) . "\n";
    
    // Create cache instance
    $cache = new ArrayCache();
    
    // Configure client with cache (300 second TTL)
    $client->setCache($cache, 300);
    
    echo "✓ Cache configured:\n";
    echo "  - Type: ArrayCache (in-memory)\n";
    echo "  - TTL: 300 seconds (5 minutes)\n\n";
    
    // ========================================
    // Example 3: Execute Query with Caching
    // ========================================
    echo "Example 3: Query Execution with Cache\n";
    echo str_repeat('-', 50) . "\n";
    
    $query = new QueryBuilder();
    $query->select(['count(*)'])
        ->from(['Transaction'])
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
    
    echo "Query: " . $query->renderNrql() . "\n\n";
    
    // First execution - will hit API
    echo "First execution (cache miss):\n";
    $startTime = microtime(true);
    $response1 = $client->query($query);
    $time1 = round((microtime(true) - $startTime) * 1000, 2);
    
    echo "  - Execution time: {$time1}ms\n";
    echo "  - Results: " . $response1->count() . " rows\n";
    
    // Check cache stats
    $stats = $client->getCacheStats();
    echo "  - Cache hits: {$stats['hits']}\n";
    echo "  - Cache misses: {$stats['misses']}\n\n";
    
    // Second execution - will hit cache
    echo "Second execution (cache hit):\n";
    $startTime = microtime(true);
    $response2 = $client->query($query);
    $time2 = round((microtime(true) - $startTime) * 1000, 2);
    
    echo "  - Execution time: {$time2}ms\n";
    echo "  - Results: " . $response2->count() . " rows\n";
    
    // Check cache stats again
    $stats = $client->getCacheStats();
    echo "  - Cache hits: {$stats['hits']}\n";
    echo "  - Cache misses: {$stats['misses']}\n";
    echo "  - Hit rate: {$stats['hit_rate']}%\n";
    echo "  - Speed improvement: " . round($time1 / $time2, 2) . "x faster\n\n";
    
    // ========================================
    // Example 4: Cache Management
    // ========================================
    echo "Example 4: Cache Management\n";
    echo str_repeat('-', 50) . "\n";
    
    // Get cache statistics
    $stats = $client->getCacheStats();
    echo "Cache statistics:\n";
    echo "  - Total hits: {$stats['hits']}\n";
    echo "  - Total misses: {$stats['misses']}\n";
    echo "  - Cached items: {$stats['size']}\n";
    echo "  - Hit rate: {$stats['hit_rate']}%\n\n";
    
    // Clear cache
    echo "Clearing cache...\n";
    $client->clearCache();
    
    $stats = $client->getCacheStats();
    echo "After clearing:\n";
    echo "  - Cached items: {$stats['size']}\n\n";
    
    // ========================================
    // Example 5: Combined Features
    // ========================================
    echo "Example 5: Retry + Cache Combined\n";
    echo str_repeat('-', 50) . "\n";
    
    // Create a new client with both features
    $retryStrategy = new ExponentialBackoff(5, 100, 10000);
    $cache = new ArrayCache();
    
    $advancedClient = new NewRelicClient($config);
    $advancedClient->setRetryStrategy($retryStrategy);
    $advancedClient->setCache($cache, 600); // 10 minute cache
    
    echo "✓ Advanced client configured:\n";
    echo "  - Retry: 5 attempts with exponential backoff\n";
    echo "  - Cache: 600 second TTL\n";
    echo "  - Benefits:\n";
    echo "    • Automatic retry on transient failures\n";
    echo "    • Reduced API calls through caching\n";
    echo "    • Better performance and reliability\n\n";
    
    // ========================================
    // Example 6: Different Retry Strategies
    // ========================================
    echo "Example 6: Custom Retry Strategies\n";
    echo str_repeat('-', 50) . "\n";
    
    // Aggressive retry (more attempts, faster)
    $aggressiveRetry = new ExponentialBackoff(
        maxAttempts: 5,
        baseDelay: 50,
        maxDelay: 2000,
        useJitter: false
    );
    
    // Conservative retry (fewer attempts, slower)
    $conservativeRetry = new ExponentialBackoff(
        maxAttempts: 2,
        baseDelay: 500,
        maxDelay: 10000,
        useJitter: true
    );
    
    echo "Aggressive strategy:\n";
    echo "  - 5 attempts, 50ms base delay\n";
    echo "  - Delays: 50ms, 100ms, 200ms, 400ms, 800ms\n\n";
    
    echo "Conservative strategy:\n";
    echo "  - 2 attempts, 500ms base delay\n";
    echo "  - Delays: 500ms, 1000ms\n\n";
    
    // ========================================
    // Example 7: Cache Benefits
    // ========================================
    echo "Example 7: Cache Performance Benefits\n";
    echo str_repeat('-', 50) . "\n";
    
    echo "Benefits of caching:\n";
    echo "  ✓ Reduced API calls (lower costs)\n";
    echo "  ✓ Faster response times\n";
    echo "  ✓ Reduced load on New Relic API\n";
    echo "  ✓ Better user experience\n";
    echo "  ✓ Offline/demo mode support\n\n";
    
    echo "Benefits of retry logic:\n";
    echo "  ✓ Handle transient network failures\n";
    echo "  ✓ Automatic recovery from temporary errors\n";
    echo "  ✓ Improved reliability\n";
    echo "  ✓ Better user experience\n";
    echo "  ✓ Reduced manual error handling\n\n";
    
    echo "=== Examples Complete ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nNote: Set NEW_RELIC_API_KEY and NEW_RELIC_ACCOUNT_ID environment variables\n";
    echo "or replace the placeholder values in the code.\n";
}

