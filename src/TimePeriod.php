<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

use InvalidArgumentException;

/**
 * Period of time of certain duration measured in one of the supported units
 */
class TimePeriod implements SyntaxRendererInterface
{
    /**
     * Units a period duration is measured in
     */
    public const UNIT_MINUTES = 'minutes';
    public const UNIT_HOURS = 'hours';
    public const UNIT_DAYS = 'days';
    public const UNIT_WEEKS = 'weeks';

    private const AVAILABLE_UNITS = [
        self::UNIT_MINUTES,
        self::UNIT_HOURS,
        self::UNIT_DAYS,
        self::UNIT_WEEKS,
    ];

    private int $duration;
    private string $unit;

    /**
     * @param int $duration Duration of the time period
     * @param string $unit One of the UNIT_* constants
     * @throws InvalidArgumentException When unit is not supported
     */
    public function __construct(int $duration, string $unit)
    {
        if (!in_array($unit, self::AVAILABLE_UNITS, true)) {
            throw new InvalidArgumentException("Unit '$unit' is not supported.");
        }
        $this->duration = $duration;
        $this->unit = $unit;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function renderNrql(): string
    {
        return $this->duration . ' ' . $this->unit;
    }
}
