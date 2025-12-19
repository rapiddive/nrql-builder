# Contributing to NRQL Builder

Thank you for considering contributing to the NRQL Builder package! This document outlines the process and guidelines.

---

## 🎯 Ways to Contribute

- 🐛 Report bugs
- 💡 Suggest new features
- 📝 Improve documentation
- 🧪 Write tests
- 💻 Submit code changes
- 🎨 Improve examples
- 🔍 Review pull requests

---

## 🚀 Getting Started

### 1. Fork and Clone

```bash
# Fork the repository on GitHub
git clone https://github.com/YOUR_USERNAME/nrql-builder.git
cd nrql-builder
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Run Tests

```bash
vendor/bin/phpunit -c test/phpunit.xml.dist
```

Make sure all tests pass before making changes.

---

## 📋 Development Workflow

### 1. Create a Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-number
```

### 2. Make Changes

- Write clean, well-documented code
- Follow PSR-12 coding standards
- Add tests for new functionality
- Update documentation

### 3. Run Tests

```bash
# Run all tests
vendor/bin/phpunit -c test/phpunit.xml.dist

# Run specific test
vendor/bin/phpunit test/NrqlBuilderTest/YourNewTest.php

# Run with coverage (requires Xdebug)
vendor/bin/phpunit -c test/phpunit.xml.dist --coverage-html coverage/
```

### 4. Check Code Quality

```bash
# Check PHP syntax
find src -name "*.php" -exec php -l {} \;

# Format code (if you have PHP-CS-Fixer)
php-cs-fixer fix
```

### 5. Commit Changes

```bash
git add .
git commit -m "feat: add amazing new feature"
```

**Commit Message Format**:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `test:` Test additions/changes
- `refactor:` Code refactoring
- `style:` Code style changes
- `chore:` Maintenance tasks

### 6. Push and Create PR

```bash
git push origin feature/your-feature-name
```

Then create a Pull Request on GitHub.

---

## 📝 Coding Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Use strict types: `declare(strict_types=1);`
- Type hint everything
- Use meaningful variable names
- Keep methods small and focused

### Example:

```php
<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

class Example
{
    public function __construct(private readonly string $value)
    {
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
}
```

### Documentation Standards

- Add PHPDoc for all public methods
- Document parameters and return types
- Include usage examples
- Explain complex logic

```php
/**
 * Execute a NRQL query and return results
 *
 * @param QueryBuilder|string $query The query to execute
 * @return QueryResponse The query results
 * @throws RuntimeException When the API request fails
 */
public function query(QueryBuilder|string $query): QueryResponse
{
    // Implementation
}
```

---

## 🧪 Testing Guidelines

### Writing Tests

1. **Test file location**: Place tests in `test/NrqlBuilderTest/` matching `src/` structure
2. **Test class naming**: `ClassNameTest` for testing `ClassName`
3. **Test method naming**: `testMethodNameScenario()`
4. **Use data providers** for testing multiple scenarios
5. **Mock external dependencies** (HTTP, databases, etc.)

### Example Test:

```php
<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Example;

class ExampleTest extends TestCase
{
    public function testGetValue(): void
    {
        $example = new Example('test');
        
        $this->assertSame('test', $example->getValue());
    }
    
    public function testConstructorThrowsExceptionOnInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Example('');
    }
}
```

### Test Coverage

- Aim for >80% code coverage
- Test happy paths and error cases
- Test edge cases
- Test public API, not implementation details

---

## 📚 Documentation Guidelines

### Code Documentation

- Document all public classes, methods, and constants
- Explain **why**, not just **what**
- Include usage examples
- Document exceptions

### README Updates

When adding features:
- Add to Features section
- Add usage example
- Update table of contents
- Add to Quick Start if relevant

### Examples

Add working examples to `examples/` directory:
- Name clearly: `feature_name_example.php`
- Include comments
- Show common use cases
- Handle errors gracefully

---

## 🐛 Reporting Bugs

### Before Reporting

1. Check if the bug is already reported
2. Verify it's not a configuration issue
3. Test with the latest version
4. Prepare a minimal reproduction

### Bug Report Template

```markdown
## Bug Description
Clear description of the bug

## Steps to Reproduce
1. Step one
2. Step two
3. ...

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 8.2
- Package Version: 1.0.0
- OS: macOS 13.0

## Code Sample
```php
// Minimal reproduction code
```

## Additional Context
Any other relevant information
```

---

## 💡 Proposing Features

### Feature Request Template

```markdown
## Feature Description
Clear description of the feature

## Use Case
Why is this feature needed?

## Proposed Solution
How should it work?

## Example Usage
```php
// How it would be used
```

## Alternatives Considered
Other approaches you've thought about

## Additional Context
Any other relevant information
```

---

## 🔍 Code Review Process

### What We Look For

✅ **Code Quality**
- Follows PSR-12 standards
- Well-documented
- Type-safe
- Clean and readable

✅ **Tests**
- All tests pass
- New tests for new features
- Good test coverage
- Tests are clear and maintainable

✅ **Documentation**
- README updated if needed
- PHPDoc added/updated
- Examples added if relevant
- CHANGELOG updated

✅ **Backward Compatibility**
- No breaking changes (unless major version)
- Deprecation notices for changed APIs
- Migration guide for breaking changes

---

## 🎨 Pull Request Guidelines

### PR Title Format

```
type: description

Examples:
feat: add query result caching
fix: handle GraphQL errors correctly
docs: improve README examples
test: add tests for Configuration class
```

### PR Description Template

```markdown
## Description
What does this PR do?

## Related Issue
Fixes #123

## Changes
- Change 1
- Change 2
- ...

## Testing
How was this tested?

## Checklist
- [ ] Tests pass
- [ ] New tests added
- [ ] Documentation updated
- [ ] CHANGELOG updated
- [ ] Code follows style guide
```

---

## 🔐 Security Issues

**Do not report security issues publicly!**

Email security issues to: [rapiddive1@gmail.com](mailto:rapiddive1@gmail.com)

Include:
- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

---

## 📞 Questions?

- **General Questions**: Open a GitHub Discussion
- **Bug Reports**: Open a GitHub Issue
- **Feature Requests**: Open a GitHub Issue
- **Security Issues**: Email rapiddive1@gmail.com

---

## 📜 License

By contributing, you agree that your contributions will be licensed under the Apache License 2.0.

---

## 🙏 Thank You!

Every contribution, no matter how small, makes this project better. Thank you for taking the time to contribute!

---

**Happy Coding! 🚀**

