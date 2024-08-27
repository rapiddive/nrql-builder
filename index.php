<?php

require_once __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;
use Rapiddive\NrqlBuilder\Moment\ExactTime;
use Rapiddive\NrqlBuilder\Moment\TimeAgo;
use Rapiddive\NrqlBuilder\Moment\Yesterday;
use Rapiddive\NrqlBuilder\QueryBuilder;
use Rapiddive\NrqlBuilder\TimePeriod;

$nrql = new QueryBuilder();
$nrql->select([
    'userAgentName',
])
    ->from([
        'PageView',
    ])
    ->where('userAgentOS = "Windows"')
    ->facet('countryCode')
    ->limit(20)
    ->since(new TimeAgo(new TimePeriod(4, TimePeriod::UNIT_DAYS)))
    ->until(new Yesterday())
    ->compareWith(new ExactTime(new Carbon('2015-01-01 00:00:00')))
    ->timeSeries(new TimePeriod(1, TimePeriod::UNIT_HOURS));

echo $nrql;