<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

use Rapiddive\NrqlBuilder\TimePeriod;

/**
 * Relative moment in the past
 */
class TimeAgo extends MomentAbstract
{
    /**
     * @param TimePeriod $period Period towards the past
     */
    public function __construct(private readonly TimePeriod $period)
    {
    }

    public function getPeriod(): TimePeriod
    {
        return $this->period;
    }

    public function renderNrql(): string
    {
        return $this->period->renderNrql() . ' AGO';
    }
}
