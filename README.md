INSPIRED FROM https://github.com/upscalesoftware/newrelic-query-builder 

[![Build Status](https://api.travis-ci.org/upscalesoftware/newrelic-query-builder.svg?branch=master)](https://travis-ci.org/upscalesoftware/newrelic-query-builder)

NRQL Builder - Query Builder & API Client for New Relic
========================================================

The New Relic Query Language (NRQL) is an SQL-flavored query language for making calls against the Insights Events database.

This project is a comprehensive PHP library that provides:

- **Query Builder**: Object-oriented NRQL query construction with fluent interface
- **REST API Client**: Execute queries via New Relic Insights Query API
- **GraphQL Client**: Execute queries via New Relic NerdGraph (GraphQL API)
- **Response Handling**: Elegant API for processing query results

The library implements the official [NRQL specification](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference) and offers a "fluent" interface to specify query parts in an arbitrary order. Query integrity validation is performed upon rendering. The library provides object-oriented representation for complex elements of NRQL syntax, enabling code completion and avoiding typos in contrast to plain text queries.

## Features

✨ **Query Builder**
- Fluent interface for building NRQL queries
- Type-safe query construction
- Support for all NRQL clauses (SELECT, FROM, WHERE, FACET, LIMIT, SINCE, UNTIL, COMPARE WITH, TIMESERIES, WITH TIMEZONE)
- Object-oriented time period representations

🚀 **API Clients**
- REST API client for Insights Query API
- GraphQL client for NerdGraph API
- Automatic response parsing
- Configurable timeouts and regions (US/EU)

📦 **Library Features**
- Easy integration with any PHP application
- PSR-4 autoloading
- Comprehensive error handling
- Iterator and ArrayAccess support for responses
- Framework-agnostic design

## Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require rapiddive/nrql-builder
```

## Requirements

- PHP 8.0 or higher
- ext-curl
- ext-json

## Quick Start

### 1. Building Queries (No API Connection)

```php
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\TimePeriod;

// Build a NRQL query
$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->where('appName = "MyApp"')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));

echo $query->renderNrql();
// Output: SELECT count(*) FROM Transaction WHERE appName = "MyApp" SINCE 1 hours AGO
```

### 2. Executing Queries via REST API

```php
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;

// Configure API credentials
$config = new Configuration(
    'YOUR_API_KEY',
    'YOUR_ACCOUNT_ID',
    Configuration::REGION_US
);

// Create client
$client = new NewRelicClient($config);

// Execute query
$response = $client->query($query);

// Access results
foreach ($response as $result) {
    echo json_encode($result) . "\n";
}
```

### 3. Executing Queries via GraphQL (NerdGraph)

```php
use Rapiddive\NrqlBuilder\Client\NerdGraphClient;

// Create NerdGraph client
$client = new NerdGraphClient($config);

// Execute query
$response = $client->query($query);

// Access results and metadata
echo "Results: " . $response->count() . "\n";
echo "Metadata: " . json_encode($response->getMetadata()) . "\n";
```

## Detailed Usage

### Query Builder Examples

#### Basic Query
```php
$query = new QueryBuilder();
$query->select(['userAgentName', 'countryCode'])
    ->from(['PageView'])
    ->limit(10);

echo $query;
// SELECT userAgentName, countryCode FROM PageView LIMIT 10
```

#### Query with Time Range
```php
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\TimePeriod;

$query = new QueryBuilder();
$query->select(['count(*)'])
    ->from(['Transaction'])
    ->since(new TimeAgo(new TimePeriod(24, TimePeriod::UNIT_HOURS)));

// SELECT count(*) FROM Transaction SINCE 24 hours AGO
```

#### Query with FACET
```php
$query = new QueryBuilder();
$query->select(['average(duration)', 'count(*)'])
    ->from(['Transaction'])
    ->facet('name')
    ->limit(20);

// SELECT average(duration), count(*) FROM Transaction FACET name LIMIT 20
```

#### Complete Query with All Features
```php
use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\Yesterday;

$query = new QueryBuilder();
$query->select(['userAgentName'])
    ->from(['PageView'])
    ->where('userAgentOS = "Windows" AND countryCode = "US"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(Carbon::parse('2024-01-01')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS))
    ->withTimezone("'America/New_York'");
```

### API Clients

#### REST API Client (Insights Query API)

The `NewRelicClient` uses the Insights Query API to execute NRQL queries:

```php
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;

$config = new Configuration('YOUR_API_KEY', 'YOUR_ACCOUNT_ID');
$client = new NewRelicClient($config);

// Execute with QueryBuilder
$response = $client->query($queryBuilder);

// Or execute with raw NRQL string
$response = $client->query("SELECT count(*) FROM Transaction SINCE 1 hour ago");

// Configure timeout
$client->setTimeout(60); // 60 seconds
```

#### GraphQL Client (NerdGraph)

The `NerdGraphClient` uses New Relic's GraphQL API for more advanced features:

```php
use Rapiddive\NrqlBuilder\Client\NerdGraphClient;

$client = new NerdGraphClient($config);

// Execute NRQL query
$response = $client->query($queryBuilder);

// Execute custom GraphQL query
$graphql = <<<'GRAPHQL'
    query($accountId: Int!) {
      actor {
        account(id: $accountId) {
          name
        }
      }
    }
    GRAPHQL;

$result = $client->executeGraphQL($graphql, ['accountId' => 123456]);
```

**Note:** NerdGraph requires a User API Key, not an Insights Query Key.

### Configuration

#### Regions

New Relic supports US and EU regions:

```php
use Rapiddive\NrqlBuilder\Config\Configuration;

// US Region (default)
$config = new Configuration($apiKey, $accountId, Configuration::REGION_US);

// EU Region
$config = new Configuration($apiKey, $accountId, Configuration::REGION_EU);
```

#### Custom URLs

Override default API endpoints for testing or proxies:

```php
$config->setInsightsQueryUrl('https://custom-proxy.example.com/query');
$config->setNerdGraphUrl('https://custom-proxy.example.com/graphql');
```

### Response Handling

The `QueryResponse` object provides multiple ways to access results:

```php
// Array access
$firstResult = $response[0];

// Iteration
foreach ($response as $result) {
    // Process each result
}

// Count results
$count = count($response);
$count = $response->count();

// Get all results
$allResults = $response->getResults();

// Get metadata
$metadata = $response->getMetadata();
$performanceStats = $response->getPerformanceStats();

// Convert to JSON
$json = $response->toJson();

// Convert to array
$array = $response->toArray();
```

### Using as a Library in Your Package

#### Service Integration

```php
class MyMonitoringService
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
}
```

#### Framework Integration (Laravel, Symfony, etc.)

```php
// Service Provider / Bundle configuration
$container->singleton(Configuration::class, function() {
    return new Configuration(
        config('newrelic.api_key'),
        config('newrelic.account_id'),
        config('newrelic.region', Configuration::REGION_US)
    );
});

$container->singleton(NewRelicClient::class, function($app) {
    return new NewRelicClient($app->make(Configuration::class));
});
```

### Time Periods and Moments

The library provides object-oriented representations for time:

#### Time Periods

```php
use Rapiddive\NrqlBuilder\TimePeriod;

// Available units
new TimePeriod(30, TimePeriod::UNIT_MINUTES);
new TimePeriod(2, TimePeriod::UNIT_HOURS);
new TimePeriod(7, TimePeriod::UNIT_DAYS);
new TimePeriod(4, TimePeriod::UNIT_WEEKS);
```

#### Moments

```php
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Carbon\Carbon;

// Relative time
$moment1 = new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS));
// Renders as: 1 hours AGO

// Yesterday
$moment2 = new Yesterday();
// Renders as: YESTERDAY

// Exact time
$moment3 = new ExactTime(Carbon::now()->subDays(7));
// Renders as: '2024-12-12 10:30:00 UTC'
```

## Examples

See the `examples/` directory for complete working examples:

- **basic_query_builder.php** - Query building without API
- **rest_api_client.php** - Using the REST API client
- **graphql_nerdgraph_client.php** - Using the GraphQL client
- **library_usage.php** - Integration examples for packages

Run examples:

```bash
php examples/basic_query_builder.php
NEW_RELIC_API_KEY=xxx NEW_RELIC_ACCOUNT_ID=yyy php examples/rest_api_client.php
```

## API Credentials

### Getting Your API Key

1. **For REST API (Insights Query Key)**:
   - Go to New Relic → Manage your data → API keys
   - Create an Insights Query Key

2. **For GraphQL (User API Key)**:
   - Go to New Relic → Manage your data → API keys
   - Create a User API Key

### Getting Your Account ID

Your Account ID is visible in the New Relic URL:
`https://one.newrelic.com/accounts/{ACCOUNT_ID}/...`

## Error Handling

Both clients throw `RuntimeException` on API errors:

```php
try {
    $response = $client->query($query);
    // Process response
} catch (\RuntimeException $e) {
    echo "API Error: " . $e->getMessage();
    // Handle error
}
```

## Limitations

Some complex aspects of the NRQL syntax have not been implemented in an object-oriented manner. These include [Aggregator Functions](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference#functions), [Math Operators](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-math) and logical operators (`AND`, `OR`, grouping). However, the library allows you to use complex expressions as strings in the appropriate places.

Free-format string arguments:
- Attributes of `SELECT` statement, including optional `AS` clause
- Conditions of `WHERE` clause
- Attribute of `FACET` clause

## Testing

Run the test suite:

```bash
vendor/bin/phpunit -c test/phpunit.xml.dist
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
