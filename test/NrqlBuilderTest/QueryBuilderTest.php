<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Moment\MomentAbstract;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $query;

    public function testSelectMissing()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("SELECT statement is missing");
        $this->query = new QueryBuilder();
        $this->query->renderNrql();
    }

    public function testFromMissing()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("FROM clause is missing");
        $this->query = new QueryBuilder();
        $this->assertSame($this->query, $this->query->selectAll());
        $this->query->renderNrql();
    }

    public function testSelectFrom()
    {
        $this->query = new QueryBuilder();
        $this->assertSame($this->query, $this->query->select(array('attr1', 'attr2')));
        $this->assertSame($this->query, $this->query->from(array('event1', 'event2')));
        $this->assertEquals('SELECT attr1, attr2 FROM event1, event2', $this->query->renderNrql());
    }

    public function testSelectAllFrom()
    {
        $this->query = new QueryBuilder();
        $this->assertSame($this->query, $this->query->selectAll());
        $this->assertSame($this->query, $this->query->from(array('event1', 'event2')));
        $this->assertEquals('SELECT * FROM event1, event2', $this->query->renderNrql());
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
        $this->assertSame($this->query, $this->query->withTimeZone("'UTC'"));
        $this->assertEquals('SELECT * FROM PageView WITH TIMEZONE \'UTC\'', $this->query->renderNrql());
    }

    public function testLimit()
    {
        $this->assertSame($this->query, $this->query->limit(10));
        $this->assertEquals('SELECT * FROM PageView LIMIT 10', $this->query->renderNrql());
    }

    public function testSince()
    {
        $moment = $this->createMomentMock('5 hours AGO');
        $this->assertSame($this->query, $this->query->since($moment));
        $this->assertEquals('SELECT * FROM PageView SINCE 5 hours AGO', $this->query->renderNrql());
        return $this->query;
    }

    /**
     * Return newly created abstract moment that renders to a specified NRQL expression
     *
     * @param string $fixtureNrql
     * @return MomentAbstract|MockObject
     */
    protected function createMomentMock(string $fixtureNrql)
    {
        $result = $this->getMockBuilder(MomentAbstract::class)
            ->getMock();
        $result
            ->expects($this->once())
            ->method('renderNrql')
            ->willReturn($fixtureNrql);
        return $result;
    }

    public function testUntil()
    {
        $moment = $this->createMomentMock('2 hours AGO');
        $this->assertSame($this->query, $this->query->until($moment));
        $this->assertEquals('SELECT * FROM PageView UNTIL 2 hours AGO', $this->query->renderNrql());
        return $this->query;
    }

    /**
     * @param QueryBuilder $query
     * @depends testSince
     */
    public function testCompareWithSince(QueryBuilder $query)
    {
        $moment = $this->createMomentMock('6 months AGO');
        $this->assertSame($query, $query->compareWith($moment));
        $this->assertEquals('SELECT * FROM PageView SINCE 5 hours AGO COMPARE WITH 6 months AGO', $query->renderNrql());
    }

    /**
     * @param QueryBuilder $query
     * @depends testUntil
     */
    public function testCompareWithUntil(QueryBuilder $query)
    {
        $moment = $this->createMomentMock('4 months AGO');
        $this->assertSame($query, $query->compareWith($moment));
        $this->assertEquals('SELECT * FROM PageView UNTIL 2 hours AGO COMPARE WITH 4 months AGO', $query->renderNrql());
    }

    public function testCompareWithAmbiguous()
    {
        $this->expectExceptionMessage("COMPARE WITH clause requires a SINCE or UNTIL clause");
        $this->expectException(LogicException::class);
        $moment = $this->createMomentMock('2 weeks AGO');
        $this->assertSame($this->query, $this->query->compareWith($moment));
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
        $period
            ->expects($this->once())
            ->method('renderNrql')
            ->willReturn('30 minutes');
        $this->assertSame($this->query, $this->query->timeSeries($period));
        $this->assertEquals('SELECT * FROM PageView TIMESERIES 30 minutes', $this->query->renderNrql());
    }

    protected function setUp(): void
    {
        $this->query = new QueryBuilder();
        $this->query
            ->selectAll()
            ->from(['PageView']);
    }
}
