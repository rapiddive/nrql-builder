<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Moment;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\ExactTime;

class ExactTimeTest extends TestCase
{
    /**
     * @var ExactTime
     */
    private ExactTime $subject;

    /**
     * @var Carbon
     */
    private Carbon $time;

    public function testGetTime()
    {
        $this->assertSame($this->time, $this->subject->getTime());
    }

    public function testRenderNrql()
    {
        $this->assertEquals("'2015-03-08 12:07:36 PDT'", $this->subject->renderNrql());
    }

    protected function setUp(): void
    {
        $this->time = new Carbon();
        $this->time
            ->setTimezone(new CarbonTimeZone('America/Los_Angeles'))
            ->setDate(2015, 3, 8)
            ->setTime(12, 7, 36);
        $this->subject = new ExactTime($this->time);
    }
}
