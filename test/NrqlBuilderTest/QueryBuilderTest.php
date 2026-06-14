<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\MomentInterface;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $query;

    protected function setUp(): void
    {
        $this->query = new QueryBuilder();
        $this->query->selectAll()->from(['PageView']);
    }

    public function testSelectMissing()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("SELECT statement is missing");
        (new QueryBuilder())->renderNrql();
    }

    public function testFromMissing()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("FROM clause is missing");
        (new QueryBuilder())->selectAll()->renderNrql();
    }

    public function testSelectFrom()
    {
        $query = (new QueryBuilder())
            ->select(['attr1', 'attr2'])
            ->from(['event1', 'event2']);
        $this->assertEquals('SELECT attr1, attr2 FROM event1, event2', $query->renderNrql());
    }

    public function testSelectAllFrom()
    {
        $query = (new QueryBuilder())->selectAll()->from(['event1', 'event2']);
        $this->assertEquals('SELECT * FROM event1, event2', $query->renderNrql());
    }

    public function testWhere()
    {
        $this->assertSame($this->query, $this->query->where('userAgentOS = "Mac"'));
        $this->assertEquals('SELECT * FROM PageView WHERE userAgentOS = "Mac"', $this->query->renderNrql());
    }

    public function testFacet()
    {
        $this->assertSame($this->query, $this->query->facet('countryCode'));
        $this->assertEquals('SELECT * FROM PageView FACET countryCode', $this->query->renderNrql());
    }

    public function testWithTimeZone()
    {
        $this->assertSame($this->query, $this->query->withTimeZone('UTC'));
        $this->assertEquals("SELECT * FROM PageView WITH TIMEZONE 'UTC'", $this->query->renderNrql());
    }

    public function testLimit()
    {
        $this->assertSame($this->query, $this->query->limit(10));
        $this->assertEquals('SELECT * FROM PageView LIMIT 10', $this->query->renderNrql());
    }

    public function testLimitZeroThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->query->limit(0);
    }

    public function testLimitNegativeThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->query->limit(-1);
    }

    public function testSince()
    {
        $moment = $this->createMomentMock('5 hours AGO');
        $this->assertSame($this->query, $this->query->since($moment));
        $this->assertEquals('SELECT * FROM PageView SINCE 5 hours AGO', $this->query->renderNrql());
    }

    public function testUntil()
    {
        $moment = $this->createMomentMock('2 hours AGO');
        $this->assertSame($this->query, $this->query->until($moment));
        $this->assertEquals('SELECT * FROM PageView UNTIL 2 hours AGO', $this->query->renderNrql());
    }

    public function testCompareWithSince()
    {
        $this->query->since($this->createMomentMock('5 hours AGO'));
        $moment = $this->createMomentMock('6 months AGO');
        $this->assertSame($this->query, $this->query->compareWith($moment));
        $this->assertEquals(
            'SELECT * FROM PageView SINCE 5 hours AGO COMPARE WITH 6 months AGO',
            $this->query->renderNrql()
        );
    }

    public function testCompareWithUntil()
    {
        $this->query->until($this->createMomentMock('2 hours AGO'));
        $moment = $this->createMomentMock('4 months AGO');
        $this->assertSame($this->query, $this->query->compareWith($moment));
        $this->assertEquals(
            'SELECT * FROM PageView UNTIL 2 hours AGO COMPARE WITH 4 months AGO',
            $this->query->renderNrql()
        );
    }

    public function testCompareWithAmbiguous()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("COMPARE WITH clause requires a SINCE or UNTIL clause");
        $this->query->compareWith($this->createMomentMock('2 weeks AGO'));
        $this->query->renderNrql();
    }

    public function testTimeSeriesAuto()
    {
        $this->assertSame($this->query, $this->query->timeSeries());
        $this->assertEquals('SELECT * FROM PageView TIMESERIES AUTO', $this->query->renderNrql());
    }

    public function testTimeSeriesPeriod()
    {
        $period = $this->getMockBuilder(TimePeriod::class)
            ->setConstructorArgs([30, TimePeriod::UNIT_MINUTES])
            ->getMock();
        $period->expects($this->once())->method('renderNrql')->willReturn('30 minutes');
        $this->assertSame($this->query, $this->query->timeSeries($period));
        $this->assertEquals('SELECT * FROM PageView TIMESERIES 30 minutes', $this->query->renderNrql());
    }

    public function testResetPartAllowsReassignment()
    {
        $this->query->where('userAgentOS = "Mac"');
        $this->query->resetPart(QueryBuilder::PART_WHERE);
        $this->query->where('userAgentOS = "Windows"');
        $this->assertEquals('SELECT * FROM PageView WHERE userAgentOS = "Windows"', $this->query->renderNrql());
    }

    public function testResetPartUnknownPartThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->query->resetPart('INVALID');
    }

    /** @return MomentInterface&MockObject */
    private function createMomentMock(string $fixtureNrql): MomentInterface
    {
        $mock = $this->createMock(MomentInterface::class);
        $mock->expects($this->once())->method('renderNrql')->willReturn($fixtureNrql);
        return $mock;
    }
}
