# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is `rapiddive/nrql-builder`, a PHP library (PHP >= 8.0) that provides a fluent, object-oriented interface for assembling [New Relic Query Language (NRQL)](https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference) queries. Inspired by [upscalesoftware/newrelic-query-builder](https://github.com/upscalesoftware/newrelic-query-builder). Key runtime dependency: `nesbot/carbon` (^3.11).

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

Everything implements `SyntaxRendererInterface` (`src/SyntaxRendererInterface.php`) — a single `renderNrql(): string` method. The two main concepts are:

**`QueryBuilder`** (`src/QueryBuilder.php`) — the entry point. Holds an ordered `$parts` array keyed by NRQL clause name constants (`PART_SELECT`, `PART_FROM`, `PART_WHERE`, `PART_FACET`, `PART_LIMIT`, `PART_SINCE`, `PART_UNTIL`, `PART_COMPARE_WITH`, `PART_TIME_SERIES`, `PART_WITH_TIMEZONE`). Public API:
- `select(array $attributes)` / `selectAll()` — sets the SELECT clause
- `from(array $events)` — sets the FROM clause
- `where(string $conditions)` — raw condition string
- `facet(string $attribute)` — raw attribute string
- `limit(int $count)` — throws `InvalidArgumentException` for values < 1
- `since(MomentInterface)` / `until(MomentInterface)` / `compareWith(MomentInterface)` — time bounds
- `timeSeries(?TimePeriod $period, string $default = 'AUTO')` — bucket size or `AUTO`
- `withTimeZone(?string $timezone)` — wraps the value in single quotes automatically; pass `null` to render nothing
- `resetPart(string $part)` — clears one clause so it can be reassigned (useful for template reuse)

All fluent methods return `static` to support subclassing. `__toString()` delegates to `renderNrql()`.

Each fluent setter calls the internal `setPart()` which enforces that a part can only be set once (throws `InvalidArgumentException` on double-set). `renderNrql()` validates required parts (`SELECT` and `FROM` are mandatory; `COMPARE WITH` requires `SINCE` or `UNTIL`) then concatenates non-empty parts in declaration order.

**Moments** (`src/Moment/`) — represent time expressions passed to `since()`, `until()`, and `compareWith()`. Hierarchy:
- `MomentInterface` extends `SyntaxRendererInterface` — implement this for custom moment types
- `MomentAbstract` — abstract base class implementing `MomentInterface`
- `TimeAgo` — wraps a `TimePeriod` and renders as `"N unit AGO"` (e.g., `4 days AGO`)
- `Yesterday` — renders as the literal `YESTERDAY`
- `ExactTime` — wraps a `CarbonInterface` instance; renders as `'Y-m-d H:i:s T'`

**`TimePeriod`** (`src/TimePeriod.php`) — a value object for duration + unit (`minutes`, `hours`, `days`, `weeks`). Constants: `UNIT_MINUTES`, `UNIT_HOURS`, `UNIT_DAYS`, `UNIT_WEEKS`. Used by `TimeAgo` and by `timeSeries()`. Renders as `"N unit"`.

### Data flow

```
QueryBuilder::since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    → TimeAgo::renderNrql() → "4 days AGO"
    → stored in QueryBuilder::$parts['SINCE']

QueryBuilder::renderNrql()
    → validates required parts
    → concatenates non-empty parts in fixed declaration order → final NRQL string
```

### Limitations (by design)

Aggregator functions, math operators, and logical operator grouping are not modelled as objects — pass them as raw strings to `select()`, `where()`, or `facet()`.

## Tests

Tests live in `test/NrqlBuilderTest/` and mirror the `src/` structure:
- `QueryBuilderTest.php` — full coverage of all clauses, validation errors, `resetPart()`, and `timeSeries()`
- `Moment/TimeAgoTest.php`, `Moment/YesterdayTest.php`, `Moment/ExactTimeTest.php`
- `TimePeriodTest.php`

The bootstrap (`test/bootstrap.php`) only requires `vendor/autoload.php`. PHPUnit config is at `test/phpunit.xml.dist`. CI runs PHPUnit 9.6 via GitHub Actions on every push (`.github/workflows/run-unit-test.yml`).

## Changelog

`CHANGELOG.md` follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) + [Semantic Versioning](https://semver.org/). Current version: **2.0.0** (2026-06-14).
