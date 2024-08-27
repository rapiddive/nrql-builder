INSPIRRED FROM https://github.com/upscalesoftware/newrelic-query-builder 

[![Build Status](https://api.travis-ci.org/upscalesoftware/newrelic-query-builder.svg?branch=master)](https://travis-ci.org/upscalesoftware/newrelic-query-builder)

Query Builder for NRQL
======================

The New Relic Query Language (NRQL) is an SQL-flavored query language for making calls against the Insights Events database.

This project is a PHP library for assembling NRQL queries in object-oriented applications. Library implements the official [NRQL specification](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference). It offers a "fluent" interface to specify query parts in an arbitrary order. That allows different application parts to influence the query w/o worrying about the query assembly order. Query integrity validation is performed upon rendering. Library provides object-oriented representation for complex elements of NRQL syntax. That enables code completion and avoids typos in contrast to plain text queries.

## Installation

Library is to be installed via [Composer](https://getcomposer.org/) as a project dependency in `composer.json`:
```yaml
{
    "require": {
        "rapiddive/nrql-builder": "*"
    }
}
```

## Usage

The example below demonstrates a query with all available clauses:
```php
use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

$nrql = new QueryBuilder();
$nrql->select([
    'userAgentName',
])
    ->from([
        'PageView',
    ])
    ->where('userAgentOS = "Windows"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(new Carbon('2015-01-01 00:00:00')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS));

echo $nrql;
```

## Limitations

Some complex aspects of the NRQL syntax have not been implemented in an object-oriented manner. These include [Aggregator Functions](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference#functions), [Math Operators](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-math) and logical operators (`AND`, `OR`, grouping). However, the library allows to utilize as complex expressions as needed in place of string arguments.
 
Free-format string arguments:
- Attributes of `SELECT` statement, including optional `AS` clause
- Conditions of `WHERE` clause
- Attribute of `FACET` clause

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
