<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

use InvalidArgumentException;

class TimePeriod implements SyntaxRendererInterface
{
    const UNIT_MINUTES = 'minutes';
    const UNIT_HOURS = 'hours';
    const UNIT_DAYS = 'days';
    const UNIT_WEEKS = 'weeks';

    private array $availableUnits = [
        self::UNIT_MINUTES,
        self::UNIT_HOURS,
        self::UNIT_DAYS,
        self::UNIT_WEEKS,
    ];

    public function __construct(private int $duration, private string $unit)
    {
        if (!in_array($unit, $this->availableUnits, true)) {
            throw new InvalidArgumentException("Unit '$unit' is not supported.");
        }
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
