<?php
/**
 * Example: Using the NerdGraph GraphQL Client
 * 
 * This example demonstrates how to use the NerdGraphClient to execute NRQL queries
 * and custom GraphQL queries against the New Relic NerdGraph API.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rapiddive\NrqlBuilder\Client\NerdGraphClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

// Configuration
// Replace these with your actual New Relic credentials
// Note: NerdGraph requires a User API Key (not Insights Query Key)
$apiKey = getenv('NEW_RELIC_USER_API_KEY') ?: 'YOUR_USER_API_KEY_HERE';
$accountId = getenv('NEW_RELIC_ACCOUNT_ID') ?: 'YOUR_ACCOUNT_ID_HERE';
$region = Configuration::REGION_US; // or Configuration::REGION_EU

echo "=== New Relic NerdGraph (GraphQL) Client Examples ===\n\n";

try {
    // Create configuration
    $config = new Configuration($apiKey, $accountId, $region);
    
    // Create NerdGraph client
    $client = new NerdGraphClient($config);
    
    // Example 1: Execute a NRQL query via NerdGraph
    echo "Example 1: NRQL query via NerdGraph\n";
    $query1 = new QueryBuilder();
    $query1->select(['count(*)'])
        ->from(['Transaction'])
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
    
    $response1 = $client->query($query1);
    echo "Query: " . $query1->renderNrql() . "\n";
    echo "Results count: " . $response1->count() . "\n";
    echo "Results: " . json_encode($response1->getResults(), JSON_PRETTY_PRINT) . "\n";
    echo "Metadata: " . json_encode($response1->getMetadata(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 2: Query with TIMESERIES
    echo "Example 2: TIMESERIES query\n";
    $query2 = new QueryBuilder();
    $query2->select(['count(*)'])
        ->from(['PageView'])
        ->since(new TimeAgo(new TimePeriod(24, TimePeriod::UNIT_HOURS)))
        ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS));
    
    $response2 = $client->query($query2);
    echo "Query: " . $query2->renderNrql() . "\n";
    echo "Results count: " . $response2->count() . "\n";
    echo "Time window: " . json_encode($response2->getMeta('timeWindow'), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 3: Query with FACET
    echo "Example 3: FACET query\n";
    $query3 = new QueryBuilder();
    $query3->select(['average(duration)', 'count(*)'])
        ->from(['Transaction'])
        ->facet('name')
        ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_DAYS)))
        ->limit(10);
    
    $response3 = $client->query($query3);
    echo "Query: " . $query3->renderNrql() . "\n";
    echo "Event types: " . json_encode($response3->getMeta('eventTypes')) . "\n";
    echo "Facets: " . json_encode($response3->getMeta('facets')) . "\n";
    
    // Iterate through results
    echo "Results:\n";
    foreach ($response3 as $result) {
        echo "  - " . json_encode($result) . "\n";
    }
    echo "\n";
    
    // Example 4: Execute raw NRQL string
    echo "Example 4: Raw NRQL string\n";
    $nrql = "SELECT count(*) FROM PageView SINCE 30 minutes ago";
    $response4 = $client->query($nrql);
    echo "Query: $nrql\n";
    echo "Results: " . json_encode($response4->getResults(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 5: Custom GraphQL query (not NRQL)
    echo "Example 5: Custom GraphQL query\n";
    $graphqlQuery = <<<'GRAPHQL'
        query($accountId: Int!) {
          actor {
            account(id: $accountId) {
              name
              licenseKey
            }
          }
        }
        GRAPHQL;
    
    $variables = ['accountId' => (int) $accountId];
    $response5 = $client->executeGraphQL($graphqlQuery, $variables);
    echo "Custom GraphQL query result:\n";
    echo json_encode($response5, JSON_PRETTY_PRINT) . "\n\n";
    
    // Example 6: Complex query with all features
    echo "Example 6: Complex query\n";
    $query6 = new QueryBuilder();
    $query6->select(['userAgentName', 'count(*)'])
        ->from(['PageView'])
        ->where('countryCode = "US"')
        ->facet('userAgentOS')
        ->since(new TimeAgo(new TimePeriod(7, TimePeriod::UNIT_DAYS)))
        ->limit(5);
    
    $response6 = $client->query($query6);
    echo "Query: " . $query6->renderNrql() . "\n";
    echo "Results: " . json_encode($response6->getResults(), JSON_PRETTY_PRINT) . "\n";
    echo "Total result: " . json_encode($response6->getMeta('totalResult')) . "\n\n";
    
    echo "=== NerdGraph Client Examples Complete ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure to set NEW_RELIC_USER_API_KEY and NEW_RELIC_ACCOUNT_ID environment variables\n";
    echo "or replace the placeholder values in the code.\n";
    echo "\nNote: NerdGraph requires a User API Key, not an Insights Query Key.\n";
}

