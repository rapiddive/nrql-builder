<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Moment;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\Yesterday;

class YesterdayTest extends TestCase
{
    /**
     * @var Yesterday
     */
    private Yesterday $subject;

    public function testRenderNrql()
    {
        $actual = $this->subject->renderNrql();
        $this->assertNotEmpty($actual);
        $this->assertSame($actual, $this->subject->renderNrql());
    }

    protected function setUp(): void
    {
        $this->subject = new Yesterday();
    }
}
