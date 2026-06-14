<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

use Rapiddive\NrqlBuilder\TimePeriod;

class TimeAgo extends MomentAbstract
{
    public function __construct(private TimePeriod $period)
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
