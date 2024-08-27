<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

use Carbon\CarbonInterface;

/**
 * Absolute moment in time
 */
class ExactTime extends MomentAbstract
{
    private CarbonInterface $time;

    /**
     * @param CarbonInterface $time
     */
    public function __construct(CarbonInterface $time)
    {
        $this->time = $time;
    }

    /**
     * @return CarbonInterface
     */
    public function getTime(): CarbonInterface
    {
        return $this->time;
    }

    /**
     * {@inheritdoc}
     */
    public function renderNrql(): string
    {
        return "'" . $this->time->format('Y-m-d H:i:s T') . "'";
    }
}
