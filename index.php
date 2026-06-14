<?php

require_once __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

// Full query with all available clauses
$nrql = (new QueryBuilder())
    ->select(['userAgentName'])
    ->from(['PageView'])
    ->where('userAgentOS = "Windows"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(new Carbon('2015-01-01 00:00:00', 'UTC')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS))
    ->withTimeZone('UTC');

echo $nrql, PHP_EOL;

// Reusing a builder as a template via resetPart()
$base = (new QueryBuilder())
    ->selectAll()
    ->from(['PageView'])
    ->where('userAgentOS = "Windows"');

echo $base, PHP_EOL;

$base->resetPart(QueryBuilder::PART_WHERE)->where('userAgentOS = "Mac"');

echo $base, PHP_EOL;
