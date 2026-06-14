<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Moment;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\ExactTime;

class ExactTimeTest extends TestCase
{
    private ExactTime $subject;
    private Carbon $time;

    public function testGetTime()
    {
        $this->assertSame($this->time, $this->subject->getTime());
    }

    public function testRenderNrql()
    {
        $this->assertEquals("'2015-03-08 12:07:36 UTC'", $this->subject->renderNrql());
    }

    protected function setUp(): void
    {
        $this->time = new Carbon('2015-03-08 12:07:36', 'UTC');
        $this->subject = new ExactTime($this->time);
    }
}
