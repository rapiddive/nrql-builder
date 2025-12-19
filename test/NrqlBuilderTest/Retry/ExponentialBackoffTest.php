<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Retry;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Retry\ExponentialBackoff;
use RuntimeException;

class ExponentialBackoffTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $strategy = new ExponentialBackoff();
        
        $this->assertSame(3, $strategy->getMaxAttempts());
    }

    public function testCustomConfiguration(): void
    {
        $strategy = new ExponentialBackoff(
            maxAttempts: 5,
            baseDelay: 200,
            maxDelay: 20000
        );
        
        $this->assertSame(5, $strategy->getMaxAttempts());
    }

    public function testShouldRetryOnRetryableException(): void
    {
        $strategy = new ExponentialBackoff();
        $exception = new RuntimeException('Temporary error');
        
        $this->assertTrue($strategy->shouldRetry(1, $exception));
        $this->assertTrue($strategy->shouldRetry(2, $exception));
        $this->assertFalse($strategy->shouldRetry(3, $exception)); // Max attempts reached
    }

    public function testShouldNotRetryOnNonRetryableException(): void
    {
        $strategy = new ExponentialBackoff();
        $exception = new \InvalidArgumentException('Invalid input');
        
        $this->assertFalse($strategy->shouldRetry(1, $exception));
    }

    public function testShouldNotRetryOn401Error(): void
    {
        $strategy = new ExponentialBackoff();
        $exception = new RuntimeException('HTTP 401: Unauthorized');
        
        $this->assertFalse($strategy->shouldRetry(1, $exception));
    }

    public function testShouldNotRetryOn403Error(): void
    {
        $strategy = new ExponentialBackoff();
        $exception = new RuntimeException('HTTP 403: Forbidden');
        
        $this->assertFalse($strategy->shouldRetry(1, $exception));
    }

    public function testShouldNotRetryOnInvalidError(): void
    {
        $strategy = new ExponentialBackoff();
        $exception = new RuntimeException('Invalid query syntax');
        
        $this->assertFalse($strategy->shouldRetry(1, $exception));
    }

    public function testExponentialDelayCalculation(): void
    {
        $strategy = new ExponentialBackoff(
            maxAttempts: 5,
            baseDelay: 100,
            maxDelay: 10000,
            useJitter: false // Disable jitter for predictable testing
        );
        
        // Attempt 1: 100 * 2^0 = 100ms
        $this->assertSame(100, $strategy->getDelay(1));
        
        // Attempt 2: 100 * 2^1 = 200ms
        $this->assertSame(200, $strategy->getDelay(2));
        
        // Attempt 3: 100 * 2^2 = 400ms
        $this->assertSame(400, $strategy->getDelay(3));
        
        // Attempt 4: 100 * 2^3 = 800ms
        $this->assertSame(800, $strategy->getDelay(4));
    }

    public function testDelayCapAtMaxDelay(): void
    {
        $strategy = new ExponentialBackoff(
            maxAttempts: 10,
            baseDelay: 1000,
            maxDelay: 5000,
            useJitter: false
        );
        
        // Attempt 5: 1000 * 2^4 = 16000ms, but capped at 5000ms
        $this->assertSame(5000, $strategy->getDelay(5));
    }

    public function testJitterAddsVariation(): void
    {
        $strategy = new ExponentialBackoff(
            maxAttempts: 3,
            baseDelay: 100,
            maxDelay: 10000,
            useJitter: true
        );
        
        // With jitter, delay should vary but be within range
        $delay1 = $strategy->getDelay(1);
        $delay2 = $strategy->getDelay(1);
        
        // Both should be around 100ms ±25%
        $this->assertGreaterThanOrEqual(75, $delay1);
        $this->assertLessThanOrEqual(125, $delay1);
        
        // They might be different due to jitter
        // (though they could be the same by chance)
    }

    public function testDelayIsNeverNegative(): void
    {
        $strategy = new ExponentialBackoff(
            maxAttempts: 3,
            baseDelay: 10,
            maxDelay: 1000,
            useJitter: true
        );
        
        for ($i = 1; $i <= 3; $i++) {
            $delay = $strategy->getDelay($i);
            $this->assertGreaterThanOrEqual(0, $delay);
        }
    }
}

