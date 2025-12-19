<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Retry;

/**
 * No-op retry strategy that never retries
 */
class NoRetry implements RetryStrategyInterface
{
    public function shouldRetry(int $attempt, \Exception $exception): bool
    {
        return false;
    }

    public function getDelay(int $attempt): int
    {
        return 0;
    }

    public function getMaxAttempts(): int
    {
        return 1;
    }
}

