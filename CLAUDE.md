# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is `rapiddive/nrql-builder`, a PHP library that provides a fluent, object-oriented interface for assembling [New Relic Query Language (NRQL)](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference) queries. Inspired by [upscalesoftware/newrelic-query-builder](https://github.com/upscalesoftware/newrelic-query-builder). PHP >= 7.4 / >= 8.0.

## Commands

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit --configuration test/phpunit.xml.dist

# Run a single test file
./vendor/bin/phpunit --configuration test/phpunit.xml.dist test/NrqlBuilderTest/QueryBuilderTest.php

# Run a single test method
./vendor/bin/phpunit --configuration test/phpunit.xml.dist --filter testSelectFrom

# Run the example script
php index.php
```

## Architecture

Everything implements `SyntaxRendererInterface` (a single `renderNrql(): string` method). The two main concepts are:

**`QueryBuilder`** (`src/QueryBuilder.php`) — the entry point. Holds an ordered `$parts` array keyed by NRQL clause names (`SELECT`, `FROM`, `WHERE`, `FACET`, `LIMIT`, `SINCE`, `UNTIL`, `COMPARE WITH`, `TIMESERIES`, `WITH TIMEZONE`). Each fluent setter calls the internal `setPart()` which enforces that a part can only be set once. `renderNrql()` validates required parts (`SELECT` and `FROM` are mandatory; `COMPARE WITH` requires `SINCE` or `UNTIL`) then concatenates non-empty parts in declaration order.

**Moments** (`src/Moment/`) — represent time expressions passed to `since()`, `until()`, and `compareWith()`. All extend `MomentAbstract`:
- `TimeAgo` — wraps a `TimePeriod` and renders as `"N unit AGO"` (e.g., `4 days AGO`)
- `Yesterday` — renders as the literal `YESTERDAY`
- `ExactTime` — wraps a `Carbon` instance and renders as a quoted datetime string

**`TimePeriod`** (`src/TimePeriod.php`) — a value object for duration + unit (`minutes`, `hours`, `days`, `weeks`). Used by `TimeAgo` and by `timeSeries()`.

### Data flow

```
QueryBuilder::since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    → TimeAgo::renderNrql() → "4 days AGO"
    → stored in QueryBuilder::$parts['SINCE']

QueryBuilder::renderNrql()
    → validates required parts
    → concatenates parts in fixed order → final NRQL string
```

### Limitations (by design)

Aggregator functions, math operators, and logical operator grouping are not modelled as objects — pass them as raw strings to `select()`, `where()`, or `facet()`.

## Tests

Tests live in `test/NrqlBuilderTest/` and mirror the `src/` structure. The bootstrap (`test/bootstrap.php`) only requires `vendor/autoload.php`. PHPUnit config is at `test/phpunit.xml.dist`. CI runs PHPUnit 9.6 via GitHub Actions on every push (`.github/workflows/run-unit-test.yml`).
