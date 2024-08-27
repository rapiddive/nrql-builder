<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\TimePeriod;

class TimePeriodTest extends TestCase
{
    /**
     * @return array{"one minute": array{0: int, 1: string, 2: string}, "five hours": array{0: int, 1: string, 2: string}, "fourteen days": array{0: int, 1: string, 2: string}, "four weeks": array{0: int, 1: string, 2: string}}
     */
    public static function durationUnitDataProvider(): array
    {
        return array(
            'one minute' => array(1, TimePeriod::UNIT_MINUTES, '1 minutes'),
            'five hours' => array(5, TimePeriod::UNIT_HOURS, '5 hours'),
            'fourteen days' => array(14, TimePeriod::UNIT_DAYS, '14 days'),
            'four weeks' => array(4, TimePeriod::UNIT_WEEKS, '4 weeks'),
        );
    }

    public function testConstructorUnsupportedUnit()
    {
        $this->expectExceptionMessage("Unit 'unsupported' is not supported");
        $this->expectException(InvalidArgumentException::class);
        new TimePeriod(1, 'unsupported');
    }

    /**
     * @param int $duration
     * @param string $unit
     * @dataProvider durationUnitDataProvider
     */
    public function testGetDuration(int $duration, string $unit)
    {
        $subject = new TimePeriod($duration, $unit);
        $this->assertEquals($duration, $subject->getDuration());
    }

    /**
     * @param int $duration
     * @param string $unit
     * @dataProvider durationUnitDataProvider
     */
    public function testGetUnit(int $duration, string $unit)
    {
        $subject = new TimePeriod($duration, $unit);
        $this->assertEquals($unit, $subject->getUnit());
    }

    /**
     * @param int $duration
     * @param string $unit
     * @param string $expected
     * @dataProvider durationUnitDataProvider
     */
    public function testRenderNrql(int $duration, string $unit, string $expected)
    {
        $subject = new TimePeriod($duration, $unit);
        $this->assertEquals($expected, $subject->renderNrql());
    }
}
