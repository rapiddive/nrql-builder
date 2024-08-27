<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

use InvalidArgumentException;

/**
 * Period of time of certain duration measured in one of the supported units
 */
class TimePeriod implements SyntaxRendererInterface
{
    /**#@+
     * Units a period duration is measured in
     */
    const UNIT_MINUTES = 'minutes';
    const UNIT_HOURS = 'hours';
    const UNIT_DAYS = 'days';
    const UNIT_WEEKS = 'weeks';
    /**#@-*/

    /**
     * @var array<string>
     */
    protected array $availableUnits = [
        self::UNIT_MINUTES,
        self::UNIT_HOURS,
        self::UNIT_DAYS,
        self::UNIT_WEEKS,
    ];

    /**
     * @var int
     */
    private int $duration;

    /**
     * @var string
     */
    private string $unit;

    /**
     * @param int $duration
     * @param string $unit Constants self::UNIT_*
     */
    public function __construct(int $duration, string $unit)
    {
        if (!in_array($unit, $this->availableUnits)) {
            throw new InvalidArgumentException("Unit '$unit' is not supported.");
        }
        $this->duration = $duration;
        $this->unit = $unit;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * {@inheritdoc}
     */
    public function renderNrql(): string
    {
        return $this->duration . ' ' . $this->unit;
    }
}
