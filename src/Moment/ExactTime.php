<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

use Carbon\CarbonInterface;

/**
 * Absolute moment in time
 */
class ExactTime extends MomentAbstract
{
    public function __construct(private readonly CarbonInterface $time)
    {
    }

    public function getTime(): CarbonInterface
    {
        return $this->time;
    }

    public function renderNrql(): string
    {
        return "'" . $this->time->format('Y-m-d H:i:s T') . "'";
    }
}
