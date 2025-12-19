<?php
/**
 * Example: Using NRQL Builder as a Library in Other Packages
 * 
 * This example demonstrates how other PHP packages can use the NRQL Builder library
 * in their applications.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Client\NerdGraphClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

/**
 * Example service class that uses NRQL Builder as a dependency
 */
class MyMonitoringService
{
    private NewRelicClient $client;
    
    public function __construct(Configuration $config)
    {
        $this->client = new NewRelicClient($config);
    }
    
    /**
     * Get error count for the last hour
     */
    public function getRecentErrorCount(): int
    {
        $query = new QueryBuilder();
        $query->select(['count(*)'])
            ->from(['TransactionError'])
            ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
        
        $response = $this->client->query($query);
        $results = $response->getResults();
        
        return $results[0]['count'] ?? 0;
    }
    
    /**
     * Get top slow transactions
     */
    public function getSlowTransactions(int $limit = 10): array
    {
        $query = new QueryBuilder();
        $query->select(['name', 'average(duration) as avgDuration', 'count(*)'])
            ->from(['Transaction'])
            ->where('duration > 1')
            ->facet('name')
            ->since(new TimeAgo(new TimePeriod(24, TimePeriod::UNIT_HOURS)))
            ->limit($limit);
        
        $response = $this->client->query($query);
        return $response->getResults();
    }
    
    /**
     * Check if error rate is above threshold
     */
    public function isErrorRateHigh(float $threshold = 5.0): bool
    {
        $query = new QueryBuilder();
        $query->select(['percentage(count(*), WHERE error IS true) as errorRate'])
            ->from(['Transaction'])
            ->since(new TimeAgo(new TimePeriod(15, TimePeriod::UNIT_MINUTES)));
        
        $response = $this->client->query($query);
        $results = $response->getResults();
        
        $errorRate = $results[0]['errorRate'] ?? 0;
        return $errorRate > $threshold;
    }
}

/**
 * Example integration with a framework (e.g., Laravel, Symfony)
 */
class FrameworkIntegration
{
    /**
     * Create NRQL Builder configuration from framework config
     */
    public static function createConfiguration(array $frameworkConfig): Configuration
    {
        return new Configuration(
            $frameworkConfig['newrelic']['api_key'],
            $frameworkConfig['newrelic']['account_id'],
            $frameworkConfig['newrelic']['region'] ?? Configuration::REGION_US
        );
    }
    
    /**
     * Register NRQL Builder in dependency injection container
     */
    public static function registerInContainer($container): void
    {
        // Example for a PSR-11 container
        $container->set(Configuration::class, function($c) {
            return self::createConfiguration($c->get('config'));
        });
        
        $container->set(NewRelicClient::class, function($c) {
            return new NewRelicClient($c->get(Configuration::class));
        });
        
        $container->set(NerdGraphClient::class, function($c) {
            return new NerdGraphClient($c->get(Configuration::class));
        });
    }
}

// Example usage
echo "=== Library Usage Examples ===\n\n";

try {
    // Simulate framework configuration
    $frameworkConfig = [
        'newrelic' => [
            'api_key' => getenv('NEW_RELIC_API_KEY') ?: 'YOUR_API_KEY',
            'account_id' => getenv('NEW_RELIC_ACCOUNT_ID') ?: 'YOUR_ACCOUNT_ID',
            'region' => 'US',
        ],
    ];
    
    // Create configuration from framework config
    $config = FrameworkIntegration::createConfiguration($frameworkConfig);
    
    // Use the monitoring service
    $service = new MyMonitoringService($config);
    
    echo "Example 1: Get recent error count\n";
    $errorCount = $service->getRecentErrorCount();
    echo "Error count (last hour): $errorCount\n\n";
    
    echo "Example 2: Get slow transactions\n";
    $slowTransactions = $service->getSlowTransactions(5);
    echo "Top 5 slow transactions:\n";
    foreach ($slowTransactions as $index => $transaction) {
        echo "  " . ($index + 1) . ". " . json_encode($transaction) . "\n";
    }
    echo "\n";
    
    echo "Example 3: Check error rate\n";
    $isHighErrorRate = $service->isErrorRateHigh(5.0);
    echo "Error rate is " . ($isHighErrorRate ? "HIGH" : "NORMAL") . "\n\n";
    
    echo "=== Library Usage Examples Complete ===\n";
    echo "\nThis demonstrates how to:\n";
    echo "- Create service classes that use NRQL Builder\n";
    echo "- Integrate with framework configuration\n";
    echo "- Register in dependency injection containers\n";
    echo "- Encapsulate New Relic queries in domain-specific methods\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Set NEW_RELIC_API_KEY and NEW_RELIC_ACCOUNT_ID environment variables to test.\n";
}

