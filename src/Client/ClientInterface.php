<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Client;

use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\Response\QueryResponse;

/**
 * Interface for New Relic API clients
 */
interface ClientInterface
{
    /**
     * Execute a NRQL query and return the results
     *
     * @param QueryBuilder|string $query NRQL query to execute
     * @return QueryResponse Query results
     * @throws \RuntimeException When the API request fails
     */
    public function query(QueryBuilder|string $query): QueryResponse;
}

