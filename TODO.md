# NRQL Builder - TODO List

This document outlines planned improvements and enhancements for the NRQL Builder package.

---

## 🔥 High Priority (Core Functionality)

### 1. PSR-18 HTTP Client Support
**Priority**: High  
**Effort**: Medium  
**Impact**: High testability, better framework integration

**Tasks**:
- [ ] Add PSR-18 HTTP client interface support
- [ ] Create adapter for cURL-based client
- [ ] Support Guzzle, Symfony HTTP Client, etc.
- [ ] Add HTTP client factory
- [ ] Update documentation

**Benefits**:
- Better testability (easier mocking)
- Framework-agnostic HTTP layer
- Support for middleware (logging, retry, etc.)
- Modern PHP standards compliance

```php
// Example usage
$httpClient = new GuzzleHttpClient();
$client = new NewRelicClient($config, $httpClient);
```

---

### 2. Query Result Caching
**Priority**: High  
**Effort**: Medium  
**Impact**: Performance, reduced API calls

**Tasks**:
- [ ] Create cache interface
- [ ] Implement PSR-6 cache adapter
- [ ] Add cache key generation
- [ ] Add TTL configuration
- [ ] Add cache invalidation
- [ ] Add cache statistics

**Benefits**:
- Reduce API calls
- Improve performance
- Lower costs
- Support offline/demo modes

```php
// Example usage
$cache = new RedisCache();
$client = new NewRelicClient($config);
$client->setCache($cache, 300); // 5 minute TTL

$response = $client->query($query); // Cached automatically
```

---

### 3. Retry Logic with Exponential Backoff
**Priority**: High  
**Effort**: Low  
**Impact**: Reliability, resilience

**Tasks**:
- [ ] Implement retry mechanism
- [ ] Add exponential backoff
- [ ] Add jitter to prevent thundering herd
- [ ] Configure max retries
- [ ] Detect retryable vs non-retryable errors
- [ ] Add retry callbacks/hooks

**Benefits**:
- Handle transient failures
- Improve reliability
- Better user experience
- Reduce manual error handling

```php
// Example usage
$client = new NewRelicClient($config);
$client->setRetryStrategy(new ExponentialBackoff(
    maxRetries: 3,
    baseDelay: 100, // ms
    maxDelay: 5000
));
```

---

## 🎯 Medium Priority (Enhanced Features)

### 4. Query Builder Helpers
**Priority**: Medium  
**Effort**: Low  
**Impact**: Developer experience

**Tasks**:
- [ ] Add common query patterns (error rate, percentiles, etc.)
- [ ] Create query templates
- [ ] Add aggregation function helpers
- [ ] Add WHERE condition builders
- [ ] Add FACET case builders
- [ ] Add time window helpers

**Benefits**:
- Faster development
- Reduce query errors
- Best practices built-in
- Code reusability

```php
// Example usage
$query = QueryBuilder::errorRate('MyApp', TimePeriod::last(1, 'hour'));
$query = QueryBuilder::percentile('duration', 95)->from('Transaction');
$query = QueryBuilder::topN('name', 10)->from('Transaction');
```

---

### 5. Rate Limiting / Throttling
**Priority**: Medium  
**Effort**: Low  
**Impact**: API compliance, cost control

**Tasks**:
- [ ] Implement rate limiter
- [ ] Add configurable limits
- [ ] Support per-endpoint limits
- [ ] Add queue mechanism
- [ ] Track API usage
- [ ] Add usage warnings/alerts

**Benefits**:
- Prevent API limit violations
- Better resource management
- Cost control
- Predictable behavior

```php
// Example usage
$rateLimiter = new RateLimiter(3000, 'per_minute');
$client = new NewRelicClient($config);
$client->setRateLimiter($rateLimiter);
```

---

### 6. Async Query Support
**Priority**: Medium  
**Effort**: High  
**Impact**: Performance for multiple queries

**Tasks**:
- [ ] Create async client interface
- [ ] Implement promise-based API
- [ ] Support concurrent queries
- [ ] Add batch query execution
- [ ] Handle errors in async context
- [ ] Add progress tracking

**Benefits**:
- Execute multiple queries in parallel
- Better performance
- Reduced total execution time
- Modern async patterns

```php
// Example usage
$client = new AsyncNewRelicClient($config);

$promise1 = $client->queryAsync($query1);
$promise2 = $client->queryAsync($query2);

Promise::all([$promise1, $promise2])->then(function($results) {
    // Process results
});
```

---

### 7. Query Validation
**Priority**: Medium  
**Effort**: Medium  
**Impact**: Error prevention

**Tasks**:
- [ ] Create query validator
- [ ] Validate NRQL syntax before sending
- [ ] Check for common mistakes
- [ ] Provide helpful error messages
- [ ] Add validation rules
- [ ] Support custom validators

**Benefits**:
- Catch errors before API call
- Better error messages
- Faster debugging
- Reduced API costs

```php
// Example usage
$validator = new NrqlValidator();
$validator->validate($query); // throws ValidationException if invalid

// Or
$query->validate(); // Built into QueryBuilder
```

---

## 💡 Low Priority (Nice to Have)

### 8. Logging & Debugging
**Priority**: Low  
**Effort**: Low  
**Impact**: Debugging, monitoring

**Tasks**:
- [ ] Add PSR-3 logger support
- [ ] Log API requests/responses
- [ ] Add debug mode
- [ ] Log query execution time
- [ ] Log cache hits/misses
- [ ] Add request/response dumping

**Benefits**:
- Easier debugging
- Better monitoring
- Audit trail
- Performance insights

```php
// Example usage
$logger = new Logger();
$client = new NewRelicClient($config);
$client->setLogger($logger);
$client->enableDebug(); // Verbose logging
```

---

### 9. Query Optimization Analyzer
**Priority**: Low  
**Effort**: Medium  
**Impact**: Performance, cost optimization

**Tasks**:
- [ ] Analyze query patterns
- [ ] Suggest optimizations
- [ ] Detect expensive queries
- [ ] Recommend indexes
- [ ] Check for anti-patterns
- [ ] Estimate query cost

**Benefits**:
- Optimize query performance
- Reduce API costs
- Best practices enforcement
- Learning tool

```php
// Example usage
$analyzer = new QueryAnalyzer();
$suggestions = $analyzer->analyze($query);
// ["Consider using FACET CASES instead of multiple WHERE conditions"]
```

---

### 10. Circuit Breaker Pattern
**Priority**: Low  
**Effort**: Medium  
**Impact**: Resilience

**Tasks**:
- [ ] Implement circuit breaker
- [ ] Configure failure thresholds
- [ ] Add half-open state
- [ ] Reset after timeout
- [ ] Track failure metrics
- [ ] Add status callbacks

**Benefits**:
- Fail fast on persistent errors
- Protect application from cascading failures
- Automatic recovery
- Better error handling

```php
// Example usage
$circuitBreaker = new CircuitBreaker(
    failureThreshold: 5,
    timeout: 60
);
$client = new NewRelicClient($config);
$client->setCircuitBreaker($circuitBreaker);
```

---

### 11. Pagination Support
**Priority**: Low  
**Effort**: Low  
**Impact**: Large dataset handling

**Tasks**:
- [ ] Add pagination helpers
- [ ] Implement cursor-based pagination
- [ ] Add page iterators
- [ ] Handle LIMIT/OFFSET automatically
- [ ] Support lazy loading
- [ ] Add pagination metadata

**Benefits**:
- Handle large result sets
- Memory efficient
- Better UX for large datasets
- Standard pagination patterns

```php
// Example usage
$paginator = $client->paginate($query, perPage: 100);
foreach ($paginator as $page) {
    foreach ($page as $result) {
        // Process result
    }
}
```

---

### 12. Query Templates
**Priority**: Low  
**Effort**: Low  
**Impact**: Code reusability

**Tasks**:
- [ ] Create template system
- [ ] Support parameter substitution
- [ ] Add template library
- [ ] Support custom templates
- [ ] Add template validation
- [ ] Support template inheritance

**Benefits**:
- Reusable query patterns
- Consistent queries
- Easier maintenance
- Team standards

```php
// Example usage
$template = QueryTemplate::load('error-rate');
$query = $template->bind([
    'appName' => 'MyApp',
    'period' => new TimePeriod(1, 'hours')
]);
```

---

## 🚀 Advanced Features (Future)

### 13. Dashboard Management
**Priority**: Future  
**Effort**: High  
**Impact**: Extended functionality

**Tasks**:
- [ ] Create dashboard client
- [ ] CRUD operations for dashboards
- [ ] Widget management
- [ ] Dashboard import/export
- [ ] Template dashboards
- [ ] Dashboard versioning

**Benefits**:
- Programmatic dashboard management
- Infrastructure as code
- Dashboard automation
- Backup/restore capabilities

---

### 14. Alert Management
**Priority**: Future  
**Effort**: High  
**Impact**: Extended functionality

**Tasks**:
- [ ] Create alert client
- [ ] CRUD operations for alerts
- [ ] Alert condition builders
- [ ] Notification channel management
- [ ] Alert testing
- [ ] Alert templates

**Benefits**:
- Programmatic alert management
- Automated alert setup
- Alert as code
- Consistent alert configuration

---

### 15. Metrics & Telemetry
**Priority**: Future  
**Effort**: Medium  
**Impact**: Observability

**Tasks**:
- [ ] Track client metrics
- [ ] Query performance metrics
- [ ] API usage metrics
- [ ] Error rates
- [ ] Export to observability tools
- [ ] Add metric dashboards

**Benefits**:
- Monitor library usage
- Identify performance issues
- Track API consumption
- Better insights

---

### 16. Query Builder GUI
**Priority**: Future  
**Effort**: Very High  
**Impact**: Non-technical users

**Tasks**:
- [ ] Create web-based query builder
- [ ] Drag-and-drop interface
- [ ] Visual query construction
- [ ] Real-time preview
- [ ] Export to PHP code
- [ ] Share queries

**Benefits**:
- Non-developer friendly
- Visual query building
- Learning tool
- Team collaboration

---

## 🧪 Testing & Quality

### 17. Integration Tests
**Priority**: Medium  
**Effort**: Low  
**Impact**: Quality assurance

**Tasks**:
- [ ] Add optional integration tests
- [ ] Test against real New Relic API
- [ ] Add test fixtures
- [ ] Support test/staging accounts
- [ ] Add E2E test suite
- [ ] Add contract tests

---

### 18. Performance Benchmarks
**Priority**: Low  
**Effort**: Low  
**Impact**: Performance monitoring

**Tasks**:
- [ ] Create benchmark suite
- [ ] Measure query building time
- [ ] Measure parsing time
- [ ] Track performance regressions
- [ ] Add benchmark CI
- [ ] Compare with alternatives

---

### 19. Code Coverage
**Priority**: Low  
**Effort**: Low  
**Impact**: Quality metrics

**Tasks**:
- [ ] Add code coverage reporting
- [ ] Set coverage targets (>80%)
- [ ] Add coverage badges
- [ ] Track coverage trends
- [ ] Add mutation testing
- [ ] Property-based testing

---

## 📚 Documentation

### 20. Enhanced Documentation
**Priority**: Medium  
**Effort**: Low  
**Impact**: Developer experience

**Tasks**:
- [ ] Add video tutorials
- [ ] Create cookbook with recipes
- [ ] Add troubleshooting guide
- [ ] Create migration guide
- [ ] Add API reference
- [ ] Add architecture docs
- [ ] Create best practices guide
- [ ] Add performance tuning guide

---

### 21. Examples & Use Cases
**Priority**: Medium  
**Effort**: Low  
**Impact**: Developer onboarding

**Tasks**:
- [ ] Add more code examples
- [ ] Real-world use cases
- [ ] Integration examples (Laravel, Symfony, etc.)
- [ ] Microservices patterns
- [ ] Monitoring examples
- [ ] Dashboard examples
- [ ] Alert examples

---

## 🔧 Infrastructure

### 22. CI/CD Enhancements
**Priority**: Low  
**Effort**: Low  
**Impact**: Development workflow

**Tasks**:
- [ ] Add automated releases
- [ ] Semantic versioning
- [ ] Changelog automation
- [ ] Deploy to Packagist automatically
- [ ] Run tests on multiple PHP versions
- [ ] Add code quality checks

---

### 23. Development Tools
**Priority**: Low  
**Effort**: Low  
**Impact**: Developer productivity

**Tasks**:
- [ ] Add CLI tool for testing queries
- [ ] Add query formatter
- [ ] Add query linter
- [ ] Add code generators
- [ ] Add debugging tools
- [ ] Add profiling tools

---

## 📊 Priority Matrix

| Feature | Priority | Effort | Impact | Score |
|---------|----------|--------|--------|-------|
| PSR-18 HTTP Client | High | Medium | High | 🔥🔥🔥 |
| Query Result Caching | High | Medium | High | 🔥🔥🔥 |
| Retry Logic | High | Low | High | 🔥🔥🔥 |
| Query Builder Helpers | Medium | Low | Medium | 🎯🎯 |
| Rate Limiting | Medium | Low | Medium | 🎯🎯 |
| Async Support | Medium | High | High | 🎯🎯🎯 |
| Query Validation | Medium | Medium | Medium | 🎯🎯 |
| Logging | Low | Low | Low | 💡 |
| Circuit Breaker | Low | Medium | Medium | 💡💡 |
| Pagination | Low | Low | Medium | 💡💡 |

---

## 🗓️ Suggested Roadmap

### Phase 1 (Q1 2024) - Foundation
- [ ] PSR-18 HTTP Client Support
- [ ] Retry Logic with Exponential Backoff
- [ ] Logging & Debugging
- [ ] Integration Tests

### Phase 2 (Q2 2024) - Performance
- [ ] Query Result Caching
- [ ] Rate Limiting
- [ ] Query Validation
- [ ] Performance Benchmarks

### Phase 3 (Q3 2024) - Advanced Features
- [ ] Async Query Support
- [ ] Query Builder Helpers
- [ ] Circuit Breaker Pattern
- [ ] Pagination Support

### Phase 4 (Q4 2024) - Extended Functionality
- [ ] Dashboard Management
- [ ] Alert Management
- [ ] Query Templates
- [ ] Enhanced Documentation

---

## 🤝 Contributing

Want to help implement these features? See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### How to Claim a TODO
1. Open an issue referencing the TODO item
2. Discuss approach and implementation details
3. Submit PR with tests and documentation
4. Get review and merge

---

## 📝 Notes

- All new features should include tests
- Maintain backward compatibility
- Update documentation
- Follow PSR standards
- Keep dependencies minimal

---

**Last Updated**: December 19, 2025  
**Package Version**: Enhanced Edition  
**Status**: Active Development

