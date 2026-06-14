# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-06-14

### Added
- `MomentInterface` — callers can now implement custom moment types without subclassing `MomentAbstract`
- `QueryBuilder::resetPart(string $part)` — clears a single clause so a builder can be reused as a template
- `QueryBuilder::limit()` now throws `InvalidArgumentException` for zero or negative values

### Changed
- Minimum PHP version raised to **8.0** (union types were already in use; implicit nullable deprecations resolved)
- `withTimeZone()` now wraps the timezone value in single quotes automatically — pass `'UTC'` instead of `"'UTC'"`
- All `QueryBuilder` fluent methods return `static` instead of `QueryBuilder` to support subclassing
- `__toString()` lets exceptions propagate naturally (PHP 8.0+) instead of converting them via `trigger_error`
- `timeSeries()` second parameter typed as `string $default = 'AUTO'`
- Constructor property promotion applied to `ExactTime`, `TimeAgo`, and `TimePeriod`
- `in_array()` in `TimePeriod` constructor is now strict

### Fixed
- `ExactTimeTest` was DST-sensitive due to a hardcoded `PDT` timezone abbreviation — pinned to UTC
- `YesterdayTest::testRenderNrql()` replaced weak `assertNotEmpty` with an exact value assertion
- `QueryBuilderTest::testCompareWith*` tests were coupled via `@depends` — made self-contained

## [1.0.2] - 2025-02-26

### Changed
- Updated `composer.json` dependencies

## [1.0.1] - 2025-01-01

### Fixed
- Minor fixes

## [1.0.0] - 2024-12-19

### Added
- Initial release with `QueryBuilder`, `TimePeriod`, and `Moment` classes (`ExactTime`, `TimeAgo`, `Yesterday`)
