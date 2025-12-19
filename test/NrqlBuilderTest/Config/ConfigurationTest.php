<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilderTest\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rapiddive\NrqlBuilder\Config\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConstructorWithValidParameters(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_US);
        
        $this->assertSame('test-api-key', $config->getApiKey());
        $this->assertSame('12345', $config->getAccountId());
        $this->assertSame(Configuration::REGION_US, $config->getRegion());
    }

    public function testConstructorDefaultsToUSRegion(): void
    {
        $config = new Configuration('test-api-key', '12345');
        
        $this->assertSame(Configuration::REGION_US, $config->getRegion());
    }

    public function testConstructorWithEURegion(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_EU);
        
        $this->assertSame(Configuration::REGION_EU, $config->getRegion());
    }

    public function testConstructorThrowsExceptionForInvalidRegion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid region 'INVALID'. Must be 'US' or 'EU'.");
        
        new Configuration('test-api-key', '12345', 'INVALID');
    }

    public function testGetInsightsQueryUrlForUSRegion(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_US);
        
        $expectedUrl = 'https://insights-api.newrelic.com/v1/accounts/12345/query';
        $this->assertSame($expectedUrl, $config->getInsightsQueryUrl());
    }

    public function testGetInsightsQueryUrlForEURegion(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_EU);
        
        $expectedUrl = 'https://insights-api.eu.newrelic.com/v1/accounts/12345/query';
        $this->assertSame($expectedUrl, $config->getInsightsQueryUrl());
    }

    public function testSetCustomInsightsQueryUrl(): void
    {
        $config = new Configuration('test-api-key', '12345');
        $customUrl = 'https://custom-proxy.example.com/query';
        
        $result = $config->setInsightsQueryUrl($customUrl);
        
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertSame($customUrl, $config->getInsightsQueryUrl());
    }

    public function testGetNerdGraphUrlForUSRegion(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_US);
        
        $expectedUrl = 'https://api.newrelic.com/graphql';
        $this->assertSame($expectedUrl, $config->getNerdGraphUrl());
    }

    public function testGetNerdGraphUrlForEURegion(): void
    {
        $config = new Configuration('test-api-key', '12345', Configuration::REGION_EU);
        
        $expectedUrl = 'https://api.eu.newrelic.com/graphql';
        $this->assertSame($expectedUrl, $config->getNerdGraphUrl());
    }

    public function testSetCustomNerdGraphUrl(): void
    {
        $config = new Configuration('test-api-key', '12345');
        $customUrl = 'https://custom-proxy.example.com/graphql';
        
        $result = $config->setNerdGraphUrl($customUrl);
        
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertSame($customUrl, $config->getNerdGraphUrl());
    }

    public function testRegionConstants(): void
    {
        $this->assertSame('US', Configuration::REGION_US);
        $this->assertSame('EU', Configuration::REGION_EU);
    }
}

