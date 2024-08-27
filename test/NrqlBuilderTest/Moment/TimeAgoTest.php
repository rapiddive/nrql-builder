<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Moment;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\TimePeriod;

class TimeAgoTest extends TestCase
{
    /**
     * @var TimeAgo
     */
    private TimeAgo $subject;

    /**
     * @var TimePeriod
     */
    private TimePeriod $period;

    public function testGetPeriod()
    {
        $this->assertSame($this->period, $this->subject->getPeriod());
    }

    public function testRenderNrql()
    {
        $this->period
            ->expects($this->once())
            ->method('renderNrql')
            ->willReturn('365 days');
        $this->assertEquals('365 days AGO', $this->subject->renderNrql());
    }

    protected function setUp(): void
    {
        $this->period = $this->getMockBuilder(TimePeriod::class)
            ->setConstructorArgs([365, TimePeriod::UNIT_DAYS])
            ->getMock();
        $this->subject = new TimeAgo($this->period);
    }
}
