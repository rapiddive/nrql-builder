<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Client;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Client\NewRelicClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Response\QueryResponse;
use RuntimeException;

// Mock client for testing
class MockNewRelicClient extends NewRelicClient
{
    public array $lastRequestUrl = [];
    public ?array $mockResponse = null;
    public bool $shouldThrowException = false;
    public ?string $exceptionMessage = null;

    protected function makeRequest(string $url): array
    {
        $this->lastRequestUrl[] = $url;
        
        if ($this->shouldThrowException) {
            throw new RuntimeException($this->exceptionMessage ?? 'Mock exception');
        }
        
        return $this->mockResponse ?? ['results' => []];
    }
    
    public function publicParseResponse(array $data): QueryResponse
    {
        return $this->parseResponse($data);
    }
}

class NewRelicClientTest extends TestCase
{
    private Configuration $config;
    private NewRelicClient $client;

    protected function setUp(): void
    {
        $this->config = new Configuration('test-api-key', '12345', Configuration::REGION_US);
        $this->client = new NewRelicClient($this->config);
    }

    public function testConstructor(): void
    {
        $client = new NewRelicClient($this->config);
        
        $this->assertInstanceOf(NewRelicClient::class, $client);
    }

    public function testConstructorWithCustomTimeout(): void
    {
        $client = new NewRelicClient($this->config, 60);
        
        $this->assertInstanceOf(NewRelicClient::class, $client);
    }

    public function testGetConfig(): void
    {
        $this->assertSame($this->config, $this->client->getConfig());
    }

    public function testSetTimeout(): void
    {
        $result = $this->client->setTimeout(45);
        
        $this->assertSame($this->client, $result); // Test fluent interface
    }

    public function testQueryWithQueryBuilder(): void
    {
        $mockClient = new MockNewRelicClient($this->config);
        $mockClient->mockResponse = [
            'results' => [
                ['count' => 100],
            ],
            'performanceStats' => [
                'executionTime' => 123,
            ],
        ];

        $query = new QueryBuilder();
        $query->select(['count(*)'])->from(['Transaction']);

        $response = $mockClient->query($query);

        $this->assertInstanceOf(QueryResponse::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertSame(100, $response->getResults()[0]['count']);
        
        // Verify URL was constructed correctly
        $this->assertCount(1, $mockClient->lastRequestUrl);
        $this->assertStringContainsString('insights-api.newrelic.com', $mockClient->lastRequestUrl[0]);
        $this->assertStringContainsString('nrql=', $mockClient->lastRequestUrl[0]);
    }

    public function testQueryWithRawNrqlString(): void
    {
        $mockClient = new MockNewRelicClient($this->config);
        $mockClient->mockResponse = [
            'results' => [['count' => 50]],
        ];

        $response = $mockClient->query("SELECT count(*) FROM Transaction");

        $this->assertInstanceOf(QueryResponse::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertStringContainsString('SELECT+count', $mockClient->lastRequestUrl[0]);
    }

    public function testParseResponse(): void
    {
        $mockClient = new MockNewRelicClient($this->config);

        $apiResponse = [
            'results' => [
                ['name' => 'Transaction1', 'count' => 100],
                ['name' => 'Transaction2', 'count' => 200],
            ],
            'performanceStats' => [
                'executionTime' => 456,
            ],
            'metadata' => [
                'eventType' => 'Transaction',
            ],
        ];

        $response = $mockClient->publicParseResponse($apiResponse);

        $this->assertSame(2, $response->count());
        $this->assertSame($apiResponse['results'], $response->getResults());
        $this->assertSame($apiResponse['performanceStats'], $response->getPerformanceStats());
        $this->assertTrue($response->getMeta('success'));
    }

    public function testParseResponseWithEmptyResults(): void
    {
        $mockClient = new MockNewRelicClient($this->config);

        $apiResponse = ['results' => []];
        $response = $mockClient->publicParseResponse($apiResponse);

        $this->assertSame(0, $response->count());
        $this->assertSame([], $response->getResults());
    }

    public function testMakeRequestThrowsExceptionOnApiError(): void
    {
        $mockClient = new MockNewRelicClient($this->config);
        $mockClient->shouldThrowException = true;
        $mockClient->exceptionMessage = 'New Relic API error (HTTP 401): Unauthorized';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('New Relic API error');

        $query = new QueryBuilder();
        $query->select(['count(*)'])->from(['Transaction']);
        $mockClient->query($query);
    }

    public function testUrlEncodingOfNrqlQuery(): void
    {
        $mockClient = new MockNewRelicClient($this->config);
        $mockClient->mockResponse = ['results' => []];

        $query = new QueryBuilder();
        $query->select(['count(*)'])
            ->from(['Transaction'])
            ->where('appName = "MyApp"');

        $mockClient->query($query);

        $url = $mockClient->lastRequestUrl[0];
        $this->assertStringContainsString('WHERE', urldecode($url));
        $this->assertStringContainsString('%3D', $url); // = sign encoded
    }

    public function testClientUsesCorrectApiEndpoint(): void
    {
        $mockClient = new MockNewRelicClient($this->config);
        $mockClient->mockResponse = ['results' => []];

        $mockClient->query("SELECT count(*) FROM Transaction");

        $this->assertStringContainsString(
            'https://insights-api.newrelic.com/v1/accounts/12345/query',
            $mockClient->lastRequestUrl[0]
        );
    }

    public function testClientUsesEUEndpointWhenConfigured(): void
    {
        $euConfig = new Configuration('test-key', '12345', Configuration::REGION_EU);
        $mockClient = new MockNewRelicClient($euConfig);
        $mockClient->mockResponse = ['results' => []];

        $mockClient->query("SELECT count(*) FROM Transaction");

        $this->assertStringContainsString(
            'https://insights-api.eu.newrelic.com/v1/accounts/12345/query',
            $mockClient->lastRequestUrl[0]
        );
    }

    public function testClientUsesCustomEndpointWhenSet(): void
    {
        $customConfig = new Configuration('test-key', '12345');
        $customConfig->setInsightsQueryUrl('https://custom-proxy.example.com/query');
        
        $mockClient = new MockNewRelicClient($customConfig);
        $mockClient->mockResponse = ['results' => []];

        $mockClient->query("SELECT count(*) FROM Transaction");

        $this->assertStringContainsString(
            'https://custom-proxy.example.com/query',
            $mockClient->lastRequestUrl[0]
        );
    }
}
