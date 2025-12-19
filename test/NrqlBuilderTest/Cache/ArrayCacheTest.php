<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Cache;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Cache\ArrayCache;
use Rapiddive\NrqlBuilder\Response\QueryResponse;

class ArrayCacheTest extends TestCase
{
    private ArrayCache $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testSetAndGet(): void
    {
        $response = new QueryResponse([['count' => 100]]);
        
        $this->cache->set('test-key', $response, 300);
        $cached = $this->cache->get('test-key');
        
        $this->assertInstanceOf(QueryResponse::class, $cached);
        $this->assertSame(100, $cached->getResults()[0]['count']);
    }

    public function testGetNonExistentKey(): void
    {
        $result = $this->cache->get('non-existent');
        
        $this->assertNull($result);
    }

    public function testHas(): void
    {
        $response = new QueryResponse([['count' => 100]]);
        
        $this->assertFalse($this->cache->has('test-key'));
        
        $this->cache->set('test-key', $response, 300);
        
        $this->assertTrue($this->cache->has('test-key'));
    }

    public function testDelete(): void
    {
        $response = new QueryResponse([['count' => 100]]);
        $this->cache->set('test-key', $response, 300);
        
        $this->assertTrue($this->cache->has('test-key'));
        
        $this->cache->delete('test-key');
        
        $this->assertFalse($this->cache->has('test-key'));
    }

    public function testClear(): void
    {
        $response1 = new QueryResponse([['count' => 100]]);
        $response2 = new QueryResponse([['count' => 200]]);
        
        $this->cache->set('key1', $response1, 300);
        $this->cache->set('key2', $response2, 300);
        
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        
        $this->cache->clear();
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testExpiration(): void
    {
        $response = new QueryResponse([['count' => 100]]);
        
        // Set with 1 second TTL
        $this->cache->set('test-key', $response, 1);
        
        $this->assertTrue($this->cache->has('test-key'));
        
        // Wait for expiration
        sleep(2);
        
        $this->assertFalse($this->cache->has('test-key'));
        $this->assertNull($this->cache->get('test-key'));
    }

    public function testStats(): void
    {
        $response = new QueryResponse([['count' => 100]]);
        
        // Initial stats
        $stats = $this->cache->getStats();
        $this->assertSame(0, $stats['hits']);
        $this->assertSame(0, $stats['misses']);
        $this->assertSame(0, $stats['size']);
        
        // Add item
        $this->cache->set('key1', $response, 300);
        
        // Miss
        $this->cache->get('non-existent');
        
        // Hit
        $this->cache->get('key1');
        $this->cache->get('key1');
        
        $stats = $this->cache->getStats();
        $this->assertSame(2, $stats['hits']);
        $this->assertSame(1, $stats['misses']);
        $this->assertSame(1, $stats['size']);
        $this->assertSame(66.67, $stats['hit_rate']);
    }

    public function testHitRateWithNoRequests(): void
    {
        $stats = $this->cache->getStats();
        $this->assertSame(0, $stats['hit_rate']);
    }
}

