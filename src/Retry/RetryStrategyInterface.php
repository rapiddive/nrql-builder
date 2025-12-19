<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Retry;

/**
 * Interface for retry strategies
 */
interface RetryStrategyInterface
{
    /**
     * Determine if a request should be retried
     *
     * @param int $attempt Current attempt number (1-indexed)
     * @param \Exception $exception The exception that occurred
     * @return bool True if should retry, false otherwise
     */
    public function shouldRetry(int $attempt, \Exception $exception): bool;

    /**
     * Calculate delay before next retry in milliseconds
     *
     * @param int $attempt Current attempt number (1-indexed)
     * @return int Delay in milliseconds
     */
    public function getDelay(int $attempt): int;

    /**
     * Get maximum number of retry attempts
     *
     * @return int Maximum attempts
     */
    public function getMaxAttempts(): int;
}

