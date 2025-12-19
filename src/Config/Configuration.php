<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Config;

use InvalidArgumentException;

/**
 * Configuration container for New Relic API credentials and settings
 */
class Configuration
{
    private string $apiKey;
    private string $accountId;
    private string $region;
    private ?string $insightsQueryUrl = null;
    private ?string $nerdGraphUrl = null;

    public const REGION_US = 'US';
    public const REGION_EU = 'EU';

    private const INSIGHTS_QUERY_URLS = [
        self::REGION_US => 'https://insights-api.newrelic.com/v1/accounts',
        self::REGION_EU => 'https://insights-api.eu.newrelic.com/v1/accounts',
    ];

    private const NERDGRAPH_URLS = [
        self::REGION_US => 'https://api.newrelic.com/graphql',
        self::REGION_EU => 'https://api.eu.newrelic.com/graphql',
    ];

    /**
     * @param string $apiKey New Relic User API Key or Insights Query Key
     * @param string $accountId New Relic Account ID
     * @param string $region Region (US or EU)
     * @throws InvalidArgumentException When region is invalid
     */
    public function __construct(string $apiKey, string $accountId, string $region = self::REGION_US)
    {
        if (!in_array($region, [self::REGION_US, self::REGION_EU], true)) {
            throw new InvalidArgumentException("Invalid region '$region'. Must be 'US' or 'EU'.");
        }

        $this->apiKey = $apiKey;
        $this->accountId = $accountId;
        $this->region = $region;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * Get the Insights Query API URL
     */
    public function getInsightsQueryUrl(): string
    {
        if ($this->insightsQueryUrl !== null) {
            return $this->insightsQueryUrl;
        }
        return self::INSIGHTS_QUERY_URLS[$this->region] . '/' . $this->accountId . '/query';
    }

    /**
     * Set a custom Insights Query API URL (useful for testing or proxies)
     */
    public function setInsightsQueryUrl(string $url): self
    {
        $this->insightsQueryUrl = $url;
        return $this;
    }

    /**
     * Get the NerdGraph (GraphQL) API URL
     */
    public function getNerdGraphUrl(): string
    {
        if ($this->nerdGraphUrl !== null) {
            return $this->nerdGraphUrl;
        }
        return self::NERDGRAPH_URLS[$this->region];
    }

    /**
     * Set a custom NerdGraph URL (useful for testing or proxies)
     */
    public function setNerdGraphUrl(string $url): self
    {
        $this->nerdGraphUrl = $url;
        return $this;
    }
}

