<?php
/**
 * Example: Basic Query Builder Usage (No API Connection)
 * 
 * This example demonstrates how to use the QueryBuilder to construct NRQL queries
 * without executing them against the New Relic API.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

echo "=== Basic Query Builder Examples ===\n\n";

// Example 1: Simple SELECT query
$query1 = new QueryBuilder();
$query1->select(['userAgentName', 'countryCode'])
    ->from(['PageView'])
    ->where('userAgentOS = "Windows"')
    ->limit(10);

echo "Query 1 (Simple SELECT):\n";
echo $query1->renderNrql() . "\n\n";

// Example 2: Query with time range
$query2 = new QueryBuilder();
$query2->select(['count(*)'])
    ->from(['Transaction'])
    ->where('appName = "MyApp"')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_HOURS)));

echo "Query 2 (With time range):\n";
echo $query2->renderNrql() . "\n\n";

// Example 3: Query with FACET
$query3 = new QueryBuilder();
$query3->select(['average(duration)', 'count(*)'])
    ->from(['Transaction'])
    ->facet('name')
    ->since(new TimeAgo(new TimePeriod(1, TimePeriod::UNIT_DAYS)))
    ->limit(20);

echo "Query 3 (With FACET):\n";
echo $query3->renderNrql() . "\n\n";

// Example 4: Query with TIMESERIES
$query4 = new QueryBuilder();
$query4->select(['count(*)'])
    ->from(['PageView'])
    ->where('countryCode = "US"')
    ->since(new TimeAgo(new TimePeriod(7, TimePeriod::UNIT_DAYS)))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS));

echo "Query 4 (With TIMESERIES):\n";
echo $query4->renderNrql() . "\n\n";

// Example 5: Complex query with all features
$query5 = new QueryBuilder();
$query5->select(['userAgentName', 'count(*)'])
    ->from(['PageView'])
    ->where('userAgentOS = "Windows" AND countryCode = "US"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(Carbon::parse('2024-01-01 00:00:00')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS))
    ->withTimezone("'America/New_York'");

echo "Query 5 (Complex with all features):\n";
echo $query5->renderNrql() . "\n\n";

// Example 6: SELECT ALL
$query6 = new QueryBuilder();
$query6->selectAll()
    ->from(['Transaction'])
    ->where('duration > 1')
    ->limit(5);

echo "Query 6 (SELECT *):\n";
echo $query6->renderNrql() . "\n\n";

echo "=== Query Builder Examples Complete ===\n";

