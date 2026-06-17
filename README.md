# NRQL Query Builder

[![CI](https://github.com/rapiddive/nrql-builder/actions/workflows/run-unit-test.yml/badge.svg)](https://github.com/rapiddive/nrql-builder/actions/workflows/run-unit-test.yml)

A PHP library for assembling [New Relic Query Language (NRQL)](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference) queries in object-oriented applications.

The library implements the official NRQL specification and offers a fluent interface to specify query parts in an arbitrary order, allowing different application parts to influence a query without worrying about assembly order. Query integrity validation is performed upon rendering. Object-oriented representations for time expressions enable code completion and avoid typos.

Inspired by [upscalesoftware/newrelic-query-builder](https://github.com/upscalesoftware/newrelic-query-builder).

## Requirements

- PHP >= 8.0
- [nesbot/carbon](https://carbon.nesbot.com/) ^3.11 (pulled in automatically via Composer)

## Installation

```bash
composer require rapiddive/nrql-builder
```

Or add it to `composer.json` manually:

```json
{
    "require": {
        "rapiddive/nrql-builder": "^2.0"
    }
}
```

## Usage

### Full query

The example below demonstrates a query using all available clauses:

```php
use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

$nrql = (new QueryBuilder())
    ->select(['userAgentName'])
    ->from(['PageView'])
    ->where('userAgentOS = "Windows"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(new Carbon('2015-01-01 00:00:00', 'UTC')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS))
    ->withTimeZone('UTC');

echo $nrql;
// SELECT userAgentName FROM PageView WHERE userAgentOS = "Windows"
// FACET countryCode LIMIT 20 SINCE 4 days AGO UNTIL YESTERDAY
// COMPARE WITH '2015-01-01 00:00:00 UTC' TIMESERIES 1 hours WITH TIMEZONE 'UTC'
```

### Select all attributes

Use `selectAll()` as a shorthand for `SELECT *`:

```php
$nrql = (new QueryBuilder())
    ->selectAll()
    ->from(['PageView']);

echo $nrql; // SELECT * FROM PageView
```

### Time series with auto bucket

Call `timeSeries()` without a period to let New Relic choose the bucket size automatically:

```php
$nrql = (new QueryBuilder())
    ->selectAll()
    ->from(['PageView'])
    ->timeSeries();

echo $nrql; // SELECT * FROM PageView TIMESERIES AUTO
```

### Reusing a builder as a template

`resetPart()` clears a single clause so it can be reassigned, letting one builder serve as a base for multiple related queries:

```php
$base = (new QueryBuilder())
    ->selectAll()
    ->from(['PageView'])
    ->where('userAgentOS = "Windows"');

echo $base; // SELECT * FROM PageView WHERE userAgentOS = "Windows"

$base->resetPart(QueryBuilder::PART_WHERE)->where('userAgentOS = "Mac"');

echo $base; // SELECT * FROM PageView WHERE userAgentOS = "Mac"
```

### Custom moment types

`since()`, `until()`, and `compareWith()` accept any implementation of `MomentInterface`, so you can define time expressions beyond the built-in `TimeAgo`, `Yesterday`, and `ExactTime`:

```php
use Rapiddive\NrqlBuilder\Moment\MomentInterface;

class BeginningOfMonth implements MomentInterface
{
    public function renderNrql(): string
    {
        return "'" . date('Y-m-01 00:00:00') . " UTC'";
    }
}

$nrql = (new QueryBuilder())
    ->selectAll()
    ->from(['PageView'])
    ->since(new BeginningOfMonth());
```

## API Reference

### `QueryBuilder` clauses

| Method | NRQL clause | Notes |
|---|---|---|
| `select(array $attributes)` | `SELECT a, b` | Comma-joins the array |
| `selectAll()` | `SELECT *` | Shorthand for wildcard |
| `from(array $events)` | `FROM Event` | Comma-joins the array |
| `where(string $conditions)` | `WHERE ...` | Raw condition string |
| `facet(string $attribute)` | `FACET attr` | Raw attribute string |
| `limit(int $count)` | `LIMIT N` | Throws on values < 1 |
| `since(MomentInterface)` | `SINCE ...` | |
| `until(MomentInterface)` | `UNTIL ...` | |
| `compareWith(MomentInterface)` | `COMPARE WITH ...` | Requires `SINCE` or `UNTIL` |
| `timeSeries(?TimePeriod, string $default='AUTO')` | `TIMESERIES ...` | `null` period uses `$default` |
| `withTimeZone(?string $timezone)` | `WITH TIMEZONE 'tz'` | Value is auto-quoted |
| `resetPart(string $part)` | — | Clears a clause for reassignment |

Use the `QueryBuilder::PART_*` constants (e.g. `QueryBuilder::PART_WHERE`) when calling `resetPart()`.

### Time expressions (`Moment`)

| Class | Renders as | Constructor |
|---|---|---|
| `TimeAgo` | `N unit AGO` | `new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS))` |
| `Yesterday` | `YESTERDAY` | `new Yesterday()` |
| `ExactTime` | `'Y-m-d H:i:s T'` | `new ExactTime(new Carbon('2024-01-01', 'UTC'))` |

### `TimePeriod` units

`TimePeriod::UNIT_MINUTES`, `UNIT_HOURS`, `UNIT_DAYS`, `UNIT_WEEKS`

## Limitations

Some complex aspects of the NRQL syntax have not been implemented in an object-oriented manner. These include [Aggregator Functions](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference#functions), [Math Operators](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-math), and logical operators (`AND`, `OR`, grouping). The library accommodates these as free-format string arguments to `select()`, `where()`, and `facet()`.

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
