<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Client;

use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Client\NerdGraphClient;
use Rapiddive\NrqlBuilder\Config\Configuration;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Response\QueryResponse;
use RuntimeException;

// Mock client for testing
class MockNerdGraphClient extends NerdGraphClient
{
    public array $lastRequestQuery = [];
    public ?array $mockResponse = null;

    protected function makeRequest(array $graphqlQuery): array
    {
        $this->lastRequestQuery[] = $graphqlQuery;
        
        $response = $this->mockResponse ?? [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [],
                        ],
                    ],
                ],
            ],
        ];
        
        // Check for errors just like the real implementation
        if (isset($response['errors'])) {
            $errorMessage = $response['errors'][0]['message'] ?? 'Unknown GraphQL error';
            throw new RuntimeException("NerdGraph API error: $errorMessage");
        }
        
        return $response;
    }
    
    public function publicBuildGraphQLQuery(string $nrql): array
    {
        return $this->buildGraphQLQuery($nrql);
    }
    
    public function publicParseResponse(array $data): QueryResponse
    {
        return $this->parseResponse($data);
    }
}

class NerdGraphClientTest extends TestCase
{
    private Configuration $config;
    private NerdGraphClient $client;

    protected function setUp(): void
    {
        $this->config = new Configuration('test-user-api-key', '12345', Configuration::REGION_US);
        $this->client = new NerdGraphClient($this->config);
    }

    public function testConstructor(): void
    {
        $client = new NerdGraphClient($this->config);
        
        $this->assertInstanceOf(NerdGraphClient::class, $client);
    }

    public function testConstructorWithCustomTimeout(): void
    {
        $client = new NerdGraphClient($this->config, 60);
        
        $this->assertInstanceOf(NerdGraphClient::class, $client);
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

    public function testBuildGraphQLQuery(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);

        $nrql = "SELECT count(*) FROM Transaction";
        $graphqlQuery = $mockClient->publicBuildGraphQLQuery($nrql);

        $this->assertArrayHasKey('query', $graphqlQuery);
        $this->assertArrayHasKey('variables', $graphqlQuery);
        $this->assertStringContainsString('query($accountId: Int!, $nrql: Nrql!)', $graphqlQuery['query']);
        $this->assertSame(12345, $graphqlQuery['variables']['accountId']);
        $this->assertSame($nrql, $graphqlQuery['variables']['nrql']);
    }

    public function testQueryWithQueryBuilder(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);
        $mockClient->mockResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [
                                ['count' => 100],
                            ],
                            'metadata' => [
                                'eventTypes' => ['Transaction'],
                                'facets' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $query = new QueryBuilder();
        $query->select(['count(*)'])->from(['Transaction']);

        $response = $mockClient->query($query);

        $this->assertInstanceOf(QueryResponse::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertSame(100, $response->getResults()[0]['count']);
        
        // Verify GraphQL query structure
        $this->assertCount(1, $mockClient->lastRequestQuery);
        $this->assertArrayHasKey('query', $mockClient->lastRequestQuery[0]);
        $this->assertArrayHasKey('variables', $mockClient->lastRequestQuery[0]);
    }

    public function testQueryWithRawNrqlString(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);
        $mockClient->mockResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [['count' => 50]],
                        ],
                    ],
                ],
            ],
        ];

        $response = $mockClient->query("SELECT count(*) FROM Transaction");

        $this->assertInstanceOf(QueryResponse::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertSame('SELECT count(*) FROM Transaction', $mockClient->lastRequestQuery[0]['variables']['nrql']);
    }

    public function testParseResponse(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);

        $graphqlResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [
                                ['name' => 'Transaction1', 'count' => 100],
                                ['name' => 'Transaction2', 'count' => 200],
                            ],
                            'totalResult' => ['count' => 300],
                            'metadata' => [
                                'eventTypes' => ['Transaction'],
                                'facets' => ['name'],
                                'timeWindow' => [
                                    'begin' => 1234567890,
                                    'end' => 1234567900,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $mockClient->publicParseResponse($graphqlResponse);

        $this->assertSame(2, $response->count());
        $this->assertSame(['Transaction'], $response->getMeta('eventTypes'));
        $this->assertSame(['name'], $response->getMeta('facets'));
        $this->assertNotNull($response->getMeta('timeWindow'));
        $this->assertSame(['count' => 300], $response->getMeta('totalResult'));
    }

    public function testParseResponseWithEmptyResults(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);

        $graphqlResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [],
                        ],
                    ],
                ],
            ],
        ];

        $response = $mockClient->publicParseResponse($graphqlResponse);

        $this->assertSame(0, $response->count());
        $this->assertSame([], $response->getResults());
    }

    public function testMakeRequestThrowsExceptionOnGraphQLError(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);
        
        // Set up mock to return error response
        $mockClient->mockResponse = [
            'errors' => [
                ['message' => 'Invalid NRQL query'],
            ],
        ];
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NerdGraph API error: Invalid NRQL query');

        $query = new QueryBuilder();
        $query->select(['count(*)'])->from(['Transaction']);
        $mockClient->query($query);
    }

    public function testExecuteGraphQL(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);
        $mockClient->mockResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'name' => 'Test Account',
                    ],
                ],
            ],
        ];

        $customQuery = <<<'GRAPHQL'
            query($accountId: Int!) {
              actor {
                account(id: $accountId) {
                  name
                }
              }
            }
            GRAPHQL;

        $result = $mockClient->executeGraphQL($customQuery, ['accountId' => 12345]);

        $this->assertArrayHasKey('data', $result);
        $this->assertSame('Test Account', $result['data']['actor']['account']['name']);
        
        // Verify the query was sent correctly
        $this->assertCount(1, $mockClient->lastRequestQuery);
        $this->assertSame(12345, $mockClient->lastRequestQuery[0]['variables']['accountId']);
    }

    public function testClientUsesCorrectApiEndpoint(): void
    {
        $url = $this->config->getNerdGraphUrl();
        
        $this->assertSame('https://api.newrelic.com/graphql', $url);
    }

    public function testClientUsesEUEndpointWhenConfigured(): void
    {
        $euConfig = new Configuration('test-key', '12345', Configuration::REGION_EU);
        $url = $euConfig->getNerdGraphUrl();
        
        $this->assertSame('https://api.eu.newrelic.com/graphql', $url);
    }

    public function testClientUsesCustomEndpointWhenSet(): void
    {
        $customConfig = new Configuration('test-key', '12345');
        $customConfig->setNerdGraphUrl('https://custom-proxy.example.com/graphql');
        
        $url = $customConfig->getNerdGraphUrl();
        
        $this->assertSame('https://custom-proxy.example.com/graphql', $url);
    }

    public function testResponseIncludesEnhancedMetadata(): void
    {
        $mockClient = new MockNerdGraphClient($this->config);
        $mockClient->mockResponse = [
            'data' => [
                'actor' => [
                    'account' => [
                        'nrql' => [
                            'results' => [['count' => 100]],
                            'metadata' => [
                                'eventTypes' => ['Transaction', 'PageView'],
                                'facets' => ['appName', 'host'],
                                'messages' => ['Query executed successfully'],
                                'timeWindow' => [
                                    'begin' => 1234567890000,
                                    'end' => 1234567900000,
                                    'compareWith' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $query = new QueryBuilder();
        $query->select(['count(*)'])->from(['Transaction']);
        $response = $mockClient->query($query);

        $this->assertSame(['Transaction', 'PageView'], $response->getMeta('eventTypes'));
        $this->assertSame(['appName', 'host'], $response->getMeta('facets'));
        $this->assertNotNull($response->getMeta('timeWindow'));
    }
}
