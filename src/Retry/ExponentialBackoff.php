<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Retry;

use RuntimeException;

/**
 * Exponential backoff retry strategy with jitter
 */
class ExponentialBackoff implements RetryStrategyInterface
{
    private int $maxAttempts;
    private int $baseDelay;
    private int $maxDelay;
    private bool $useJitter;
    private array $retryableExceptions;

    /**
     * @param int $maxAttempts Maximum number of retry attempts (default: 3)
     * @param int $baseDelay Base delay in milliseconds (default: 100)
     * @param int $maxDelay Maximum delay in milliseconds (default: 10000)
     * @param bool $useJitter Add random jitter to prevent thundering herd (default: true)
     * @param array $retryableExceptions List of exception classes that should trigger retry
     */
    public function __construct(
        int $maxAttempts = 3,
        int $baseDelay = 100,
        int $maxDelay = 10000,
        bool $useJitter = true,
        array $retryableExceptions = [RuntimeException::class]
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->baseDelay = $baseDelay;
        $this->maxDelay = $maxDelay;
        $this->useJitter = $useJitter;
        $this->retryableExceptions = $retryableExceptions;
    }

    public function shouldRetry(int $attempt, \Exception $exception): bool
    {
        // Don't retry if max attempts reached
        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        // Check if exception is retryable
        foreach ($this->retryableExceptions as $retryableClass) {
            if ($exception instanceof $retryableClass) {
                // Check for specific non-retryable errors
                if ($this->isNonRetryableError($exception)) {
                    return false;
                }
                return true;
            }
        }

        return false;
    }

    public function getDelay(int $attempt): int
    {
        // Calculate exponential delay: baseDelay * 2^(attempt - 1)
        $delay = $this->baseDelay * (2 ** ($attempt - 1));

        // Cap at max delay
        $delay = min($delay, $this->maxDelay);

        // Add jitter if enabled (random variation of ±25%)
        if ($this->useJitter) {
            $jitter = $delay * 0.25;
            $delay = $delay + random_int((int)(-$jitter), (int)$jitter);
        }

        return max(0, (int)$delay);
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * Check if error is non-retryable (client errors, auth errors, etc.)
     */
    private function isNonRetryableError(\Exception $exception): bool
    {
        $message = $exception->getMessage();

        // Authentication/Authorization errors (4xx)
        if (preg_match('/HTTP (401|403|404)/', $message)) {
            return true;
        }

        // Invalid request errors
        if (stripos($message, 'invalid') !== false) {
            return true;
        }

        // Validation errors
        if (stripos($message, 'validation') !== false) {
            return true;
        }

        return false;
    }
}

