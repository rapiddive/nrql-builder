<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Response;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Response\QueryResponse;

class QueryResponseTest extends TestCase
{
    private array $sampleResults;
    private array $sampleMetadata;

    protected function setUp(): void
    {
        $this->sampleResults = [
            ['count' => 100, 'name' => 'Transaction1'],
            ['count' => 200, 'name' => 'Transaction2'],
            ['count' => 150, 'name' => 'Transaction3'],
        ];

        $this->sampleMetadata = [
            'performanceStats' => ['executionTime' => 123],
            'totalCount' => 3,
            'success' => true,
        ];
    }

    public function testConstructorAndGetResults(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $this->assertSame($this->sampleResults, $response->getResults());
    }

    public function testGetMetadata(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $this->assertSame($this->sampleMetadata, $response->getMetadata());
    }

    public function testGetMeta(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $this->assertSame(['executionTime' => 123], $response->getMeta('performanceStats'));
        $this->assertSame(3, $response->getMeta('totalCount'));
        $this->assertNull($response->getMeta('nonexistent'));
        $this->assertSame('default', $response->getMeta('nonexistent', 'default'));
    }

    public function testGetTotalCount(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $this->assertSame(3, $response->getTotalCount());
    }

    public function testGetTotalCountFallsBackToResultsCount(): void
    {
        $response = new QueryResponse($this->sampleResults, []);
        
        $this->assertSame(3, $response->getTotalCount());
    }

    public function testGetPerformanceStats(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $this->assertSame(['executionTime' => 123], $response->getPerformanceStats());
    }

    public function testGetPerformanceStatsReturnsNullWhenNotSet(): void
    {
        $response = new QueryResponse($this->sampleResults, []);
        
        $this->assertNull($response->getPerformanceStats());
    }

    public function testIsSuccess(): void
    {
        $response = new QueryResponse($this->sampleResults, ['success' => true]);
        $this->assertTrue($response->isSuccess());
        
        $response2 = new QueryResponse($this->sampleResults, []);
        $this->assertTrue($response2->isSuccess()); // Has results
        
        $response3 = new QueryResponse([], []);
        $this->assertFalse($response3->isSuccess()); // No results, no success flag
    }

    // Iterator tests
    public function testIteratorInterface(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $count = 0;
        foreach ($response as $key => $result) {
            $this->assertSame($this->sampleResults[$key], $result);
            $count++;
        }
        
        $this->assertSame(3, $count);
    }

    public function testIteratorRewind(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        // First iteration
        foreach ($response as $result) {
            break; // Stop after first
        }
        
        // Second iteration should start from beginning
        $count = 0;
        foreach ($response as $result) {
            $count++;
        }
        
        $this->assertSame(3, $count);
    }

    // Countable tests
    public function testCountableInterface(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $this->assertSame(3, count($response));
        $this->assertSame(3, $response->count());
    }

    public function testCountWithEmptyResults(): void
    {
        $response = new QueryResponse([]);
        
        $this->assertSame(0, count($response));
    }

    // ArrayAccess tests
    public function testArrayAccessOffsetExists(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $this->assertTrue(isset($response[0]));
        $this->assertTrue(isset($response[1]));
        $this->assertTrue(isset($response[2]));
        $this->assertFalse(isset($response[3]));
    }

    public function testArrayAccessOffsetGet(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $this->assertSame($this->sampleResults[0], $response[0]);
        $this->assertSame($this->sampleResults[1], $response[1]);
        $this->assertSame($this->sampleResults[2], $response[2]);
        $this->assertNull($response[999]);
    }

    public function testArrayAccessOffsetSet(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $newResult = ['count' => 300, 'name' => 'Transaction4'];
        $response[3] = $newResult;
        
        $this->assertSame($newResult, $response[3]);
    }

    public function testArrayAccessOffsetSetWithNullKey(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        $newResult = ['count' => 300, 'name' => 'Transaction4'];
        $response[] = $newResult;
        
        $this->assertSame($newResult, $response[3]);
    }

    public function testArrayAccessOffsetUnset(): void
    {
        $response = new QueryResponse($this->sampleResults);
        
        unset($response[1]);
        
        $this->assertFalse(isset($response[1]));
    }

    // Conversion tests
    public function testToJson(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $json = $response->toJson();
        $decoded = json_decode($json, true);
        
        $this->assertSame($this->sampleResults, $decoded['results']);
        $this->assertSame($this->sampleMetadata, $decoded['metadata']);
    }

    public function testToArray(): void
    {
        $response = new QueryResponse($this->sampleResults, $this->sampleMetadata);
        
        $array = $response->toArray();
        
        $this->assertSame($this->sampleResults, $array['results']);
        $this->assertSame($this->sampleMetadata, $array['metadata']);
    }

    public function testEmptyResponse(): void
    {
        $response = new QueryResponse([]);
        
        $this->assertSame([], $response->getResults());
        $this->assertSame(0, count($response));
        $this->assertFalse($response->isSuccess());
    }

    public function testResponseWithOnlyMetadata(): void
    {
        $metadata = ['eventTypes' => ['Transaction'], 'facets' => ['name']];
        $response = new QueryResponse([], $metadata);
        
        $this->assertSame([], $response->getResults());
        $this->assertSame($metadata, $response->getMetadata());
        $this->assertSame(['Transaction'], $response->getMeta('eventTypes'));
    }
}

