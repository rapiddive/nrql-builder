# NRQL Builder - Quick Start Guide

## 🚀 Installation

```bash
composer require rapiddive/nrql-builder
```

## 📋 Table of Contents

1. [Query Builder Only](#query-builder-only)
2. [REST API Client](#rest-api-client)
3. [GraphQL Client (NerdGraph)](#graphql-client-nerdgraph)
4. [Using in Your Package](#using-in-your-package)

---

## Query Builder Only

Build NRQL queries without executing them:

```php
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\TimePeriod;

$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->where('appName = "MyApp"')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));

echo $query->renderNrql();
// SELECT count(*) FROM Transaction WHERE appName = "MyApp" SINCE 1 hours AGO
```

---

## REST API Client

Execute queries via New Relic Insights Query API:

### Setup

```php
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;

// Create configuration
$config = new Configuration(
    'YOUR_INSIGHTS_QUERY_KEY',  // Get from New Relic → API Keys
    'YOUR_ACCOUNT_ID',           // Found in New Relic URL
    Configuration::REGION_US     // or REGION_EU
);

// Create client
$client = new NewRelicClient($config);
```

### Execute Queries

```php
// With QueryBuilder
$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));

$response = $client->query($query);

// Or with raw NRQL string
$response = $client->query("SELECT count(*) FROM Transaction SINCE 1 hour ago");
```

### Process Results

```php
// Get all results
$results = $response->getResults();

// Iterate
foreach ($response as $result) {
    echo json_encode($result) . "\n";
}

// Count
$count = $response->count();

// Metadata
$metadata = $response->getMetadata();
$perfStats = $response->getPerformanceStats();
```

---

## GraphQL Client (NerdGraph)

Execute queries via New Relic's GraphQL API:

### Setup

```php
use Rapiddive\NrqlBuilder\Client\NerdGraphClient;
use Rapiddive\NrqlBuilder\Config\Configuration;

// Create configuration (requires User API Key, not Insights Query Key)
$config = new Configuration(
    'YOUR_USER_API_KEY',    // Get from New Relic → API Keys (User key)
    'YOUR_ACCOUNT_ID',
    Configuration::REGION_US
);

// Create GraphQL client
$client = new NerdGraphClient($config);
```

### Execute NRQL Queries

```php
$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->since(new TimeAgo(new TimePeriod(24, TimePeriod::UNIT_HOURS)));

$response = $client->query($query);

// Access enhanced metadata from GraphQL
echo "Event types: " . json_encode($response->getMeta('eventTypes'));
echo "Facets: " . json_encode($response->getMeta('facets'));
echo "Time window: " . json_encode($response->getMeta('timeWindow'));
```

### Execute Custom GraphQL

```php
$graphql = <<<'GRAPHQL'
    query($accountId: Int!) {
      actor {
        account(id: $accountId) {
          name
          licenseKey
        }
      }
    }
    GRAPHQL;

$result = $client->executeGraphQL($graphql, ['accountId' => 123456]);
```

---

## Using in Your Package

### Create a Service Class

```php
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\TimePeriod;

class MonitoringService
{
    private NewRelicClient $client;
    
    public function __construct(Configuration $config)
    {
        $this->client = new NewRelicClient($config);
    }
    
    public function getErrorCount(): int
    {
        $query = new QueryBuilder();
        $query->select(['count(*)'])
            ->from(['TransactionError'])
            ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
        
        $response = $this->client->query($query);
        return $response->getResults()[0]['count'] ?? 0;
    }
    
    public function getSlowTransactions(int $limit = 10): array
    {
        $query = new QueryBuilder();
        $query->select(['name', 'average(duration)', 'count(*)'])
            ->from(['Transaction'])
            ->where('duration > 1')
            ->facet('name')
            ->since(new TimeAgo(new TimePeriod(24, TimePeriod::UNIT_HOURS)))
            ->limit($limit);
        
        $response = $this->client->query($query);
        return $response->getResults();
    }
}
```

### Framework Integration

#### Laravel

```php
// config/newrelic.php
return [
    'api_key' => env('NEW_RELIC_API_KEY'),
    'account_id' => env('NEW_RELIC_ACCOUNT_ID'),
    'region' => env('NEW_RELIC_REGION', 'US'),
];

// AppServiceProvider.php
public function register()
{
    $this->app->singleton(Configuration::class, function ($app) {
        return new Configuration(
            config('newrelic.api_key'),
            config('newrelic.account_id'),
            config('newrelic.region')
        );
    });
    
    $this->app->singleton(NewRelicClient::class, function ($app) {
        return new NewRelicClient($app->make(Configuration::class));
    });
}

// Controller or Service
public function __construct(NewRelicClient $client)
{
    $this->client = $client;
}
```

#### Symfony

```yaml
# config/services.yaml
services:
    Rapiddive\NrqlBuilder\Config\Configuration:
        arguments:
            $apiKey: '%env(NEW_RELIC_API_KEY)%'
            $accountId: '%env(NEW_RELIC_ACCOUNT_ID)%'
            $region: 'US'
    
    Rapiddive\NrqlBuilder\Client\NewRelicClient:
        arguments:
            $config: '@Rapiddive\NrqlBuilder\Config\Configuration'
```

---

## 📚 Common Query Patterns

### Error Rate Monitoring

```php
$query = new QueryBuilder();
$query->select(['percentage(count(*), WHERE error IS true) as errorRate'])
    ->from(['Transaction'])
    ->since(new TimeAgo(new TimePeriod(15, TimePeriod::UNIT_MINUTES)));
```

### Page Views by Country

```php
$query = new QueryBuilder();
$query->select(['count(*) as views'])
    ->from(['PageView'])
    ->facet('countryCode')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_DAYS)))
    ->limit(20);
```

### Average Response Time

```php
$query = new QueryBuilder();
$query->select(['average(duration) as avgDuration'])
    ->from(['Transaction'])
    ->where('transactionType = "Web"')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));
```

### Time Series Data

```php
$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->since(new TimeAgo(new TimePeriod(7, TimePeriod::UNIT_DAYS)))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS));
```

---

## 🔑 Getting Your Credentials

### API Keys

1. Log in to New Relic
2. Go to **Account settings** → **API keys**
3. For REST API: Create an **Insights Query Key**
4. For GraphQL: Create a **User API Key**

### Account ID

Your Account ID is in the URL: `https://one.newrelic.com/accounts/{ACCOUNT_ID}/...`

---

## ⚙️ Configuration Options

### Regions

```php
// US Region (default)
$config = new Configuration($apiKey, $accountId, Configuration::REGION_US);

// EU Region
$config = new Configuration($apiKey, $accountId, Configuration::REGION_EU);
```

### Timeouts

```php
// Default: 30 seconds
$client = new NewRelicClient($config, 30);

// Change timeout
$client->setTimeout(60);
```

### Custom URLs (for proxies/testing)

```php
$config->setInsightsQueryUrl('https://proxy.example.com/query');
$config->setNerdGraphUrl('https://proxy.example.com/graphql');
```

---

## 🛠️ Time Periods

```php
use Rapiddive\NrqlBuilder\TimePeriod;

TimePeriod::UNIT_MINUTES  // minutes
TimePeriod::UNIT_HOURS    // hours  
TimePeriod::UNIT_DAYS     // days
TimePeriod::UNIT_WEEKS    // weeks

// Examples
new TimePeriod(30, TimePeriod::UNIT_MINUTES);
new TimePeriod(2, TimePeriod::UNIT_HOURS);
new TimePeriod(7, TimePeriod::UNIT_DAYS);
```

## 🕐 Time Moments

```php
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Carbon\Carbon;

// Relative time
new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS))
// → "1 hours AGO"

// Yesterday
new Yesterday()
// → "YESTERDAY"

// Exact time
new ExactTime(Carbon::parse('2024-01-01'))
// → "'2024-01-01 00:00:00 UTC'"
```

---

## ❗ Error Handling

```php
try {
    $response = $client->query($query);
    // Process response
} catch (\RuntimeException $e) {
    // API error occurred
    echo "Error: " . $e->getMessage();
}
```

---

## 📖 More Examples

Check the `examples/` directory:

- `basic_query_builder.php` - Query building
- `rest_api_client.php` - REST API usage
- `graphql_nerdgraph_client.php` - GraphQL usage  
- `library_usage.php` - Package integration

Run:
```bash
php examples/basic_query_builder.php
NEW_RELIC_API_KEY=xxx NEW_RELIC_ACCOUNT_ID=yyy php examples/rest_api_client.php
```

---

## 🔗 Resources

- [NRQL Reference](https://docs.newrelic.com/docs/query-your-data/nrql-new-relic-query-language/get-started/introduction-nrql-new-relics-query-language/)
- [New Relic API Keys](https://docs.newrelic.com/docs/apis/intro-apis/new-relic-api-keys/)
- [NerdGraph API](https://docs.newrelic.com/docs/apis/nerdgraph/get-started/introduction-new-relic-nerdgraph/)

---

**Need help?** Check the full [README.md](README.md) or create an issue!

