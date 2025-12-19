<?php
/**
 * Example: Using the REST API Client
 * 
 * This example demonstrates how to use the NewRelicClient to execute NRQL queries
 * against the New Relic Insights Query API (REST).
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

// Configuration
// Replace these with your actual New Relic credentials
$apiKey = getenv('NEW_RELIC_API_KEY') ?: 'YOUR_API_KEY_HERE';
$accountId = getenv('NEW_RELIC_ACCOUNT_ID') ?: 'YOUR_ACCOUNT_ID_HERE';
$region = Configuration::REGION_US; // or Configuration::REGION_EU

echo "=== New Relic REST API Client Examples ===\n\n";

try {
    // Create configuration
    $config = new Configuration($apiKey, $accountId, $region);
    
    // Create REST API client
    $client = new NewRelicClient($config);
    
    // Example 1: Execute a simple query
    echo "Example 1: Simple query\n";
    $query1 = new QueryBuilder();
    $query1->select(['count(*)'])
        ->from(['Transaction'])
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
    
    $response1 = $client->query($query1);
    echo "Query: " . $query1->renderNrql() . "\n";
    echo "Results count: " . $response1->count() . "\n";
    echo "Results: " . json_encode($response1->getResults(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 2: Execute query with raw NRQL string
    echo "Example 2: Raw NRQL string\n";
    $nrql = "SELECT count(*) FROM PageView SINCE 30 minutes ago";
    $response2 = $client->query($nrql);
    echo "Query: $nrql\n";
    echo "Results: " . json_encode($response2->getResults(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 3: Query with FACET
    echo "Example 3: Query with FACET\n";
    $query3 = new QueryBuilder();
    $query3->select(['average(duration)', 'count(*)'])
        ->from(['Transaction'])
        ->facet('name')
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_DAYS)))
        ->limit(10);
    
    $response3 = $client->query($query3);
    echo "Query: " . $query3->renderNrql() . "\n";
    echo "Results count: " . $response3->count() . "\n";
    
    // Iterate through results
    echo "Top transactions by count:\n";
    foreach ($response3 as $index => $result) {
        echo "  " . ($index + 1) . ". " . json_encode($result) . "\n";
    }
    echo "\n";
    
    // Example 4: Using response metadata
    echo "Example 4: Response metadata\n";
    $query4 = new QueryBuilder();
    $query4->select(['count(*)'])
        ->from(['PageView'])
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
    
    $response4 = $client->query($query4);
    echo "Query: " . $query4->renderNrql() . "\n";
    echo "Performance stats: " . json_encode($response4->getPerformanceStats(), JSON_PRETTY_PRINT) . "\n";
    echo "Metadata: " . json_encode($response4->getMetadata(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 5: Custom timeout
    echo "Example 5: Custom timeout\n";
    $client->setTimeout(60); // 60 seconds
    $query5 = new QueryBuilder();
    $query5->selectAll()
        ->from(['Transaction'])
        ->limit(5);
    
    $response5 = $client->query($query5);
    echo "Query: " . $query5->renderNrql() . "\n";
    echo "Results: " . json_encode($response5->getResults(), JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== REST API Client Examples Complete ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure to set NEW_RELIC_API_KEY and NEW_RELIC_ACCOUNT_ID environment variables\n";
    echo "or replace the placeholder values in the code.\n";
}

