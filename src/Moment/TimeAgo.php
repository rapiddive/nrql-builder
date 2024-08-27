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
     * @var TimePeriod
     */
    private TimePeriod $period;

    /**
     * @param TimePeriod $period Period towards the past
     */
    public function __construct(TimePeriod $period)
    {
        $this->period = $period;
    }

    /**
     * @return TimePeriod
     */
    public function getPeriod(): TimePeriod
    {
        return $this->period;
    }

    /**
     * {@inheritdoc}
     */
    public function renderNrql(): string
    {
        return $this->period->renderNrql() . ' AGO';
    }
}
