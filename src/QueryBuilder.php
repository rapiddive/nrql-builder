<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

use Exception;
use InvalidArgumentException;
use LogicException;
use Rapiddive\NrqlBuilder\Moment\MomentAbstract;

/**
 * Builder of a query in New Relic Query Language (NRQL) with fluent interface to set query parts in an arbitrary order
 *
 * @link https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference
 */
class QueryBuilder implements SyntaxRendererInterface
{
    /**
     * Available parts of a query in NRQL
     */
    public const PART_SELECT = 'SELECT';
    public const PART_FROM = 'FROM';
    public const PART_WHERE = 'WHERE';
    public const PART_FACET = 'FACET';
    public const PART_LIMIT = 'LIMIT';
    public const PART_SINCE = 'SINCE';
    public const PART_UNTIL = 'UNTIL';
    public const PART_COMPARE_WITH = 'COMPARE WITH';
    public const PART_TIME_SERIES = 'TIMESERIES';
    public const PART_WITH_TIMEZONE = 'WITH TIMEZONE';

    /**
     * Rendered parts of a query
     *
     * @var array<string,mixed>
     */
    protected array $parts = [
        self::PART_SELECT => '',
        self::PART_FROM => '',
        self::PART_WHERE => '',
        self::PART_FACET => '',
        self::PART_LIMIT => '',
        self::PART_SINCE => '',
        self::PART_UNTIL => '',
        self::PART_COMPARE_WITH => '',
        self::PART_TIME_SERIES => '',
        self::PART_WITH_TIMEZONE => ''
    ];

    public function withTimezone(?string $timezone = null): self
    {
        return $this->setPart(self::PART_WITH_TIMEZONE, $timezone ?? '');
    }

    /**
     * Assign NRQL expression to a query part with no syntax validation
     *
     * @throws InvalidArgumentException When specified query part is not supported
     * @throws InvalidArgumentException When attempting to override existing value of a query part
     */
    protected function setPart(string $part, string|int $value): self
    {
        if (!array_key_exists($part, $this->parts)) {
            throw new InvalidArgumentException("Query part '$part' is not recognized.");
        }
        if ($this->parts[$part] !== '') {
            throw new InvalidArgumentException("Value has already been assigned to the query part '$part'.");
        }
        $this->parts[$part] = $value;
        return $this;
    }

    /**
     * Assign SELECT statement to specify what the query is reporting
     */
    public function select(array $attributes): self
    {
        return $this->setPart(self::PART_SELECT, implode(', ', $attributes));
    }

    /**
     * Assign SELECT statement to specify that the query is reporting all available attributes
     */
    public function selectAll(): self
    {
        return $this->setPart(self::PART_SELECT, '*');
    }

    /**
     * Assign FROM clause to specify the event type(s) containing the attributes being queried
     */
    public function from(array $events): self
    {
        return $this->setPart(self::PART_FROM, implode(', ', $events));
    }

    /**
     * Assign WHERE clause to specify a series of one or more conditions separated by the keywords AND or OR
     */
    public function where(string $conditions): self
    {
        return $this->setPart(self::PART_WHERE, $conditions);
    }

    /**
     * Assign FACET clause to break out your data by any string attribute
     */
    public function facet(string $attribute): self
    {
        return $this->setPart(self::PART_FACET, $attribute);
    }

    /**
     * Assign LIMIT clause to constrain the number of values returned
     */
    public function limit(int $count): self
    {
        return $this->setPart(self::PART_LIMIT, $count);
    }

    /**
     * Assign SINCE clause to define the beginning of a time range across which to pull data
     */
    public function since(MomentAbstract $moment): self
    {
        return $this->setPart(self::PART_SINCE, $moment->renderNrql());
    }

    /**
     * Return complete query assembled from individual pieces
     */
    public function renderNrql(): string
    {
        $this->validate();
        $result = '';
        foreach ($this->parts as $name => $value) {
            if ($value !== '' && $value !== 0) {
                $result .= ($result !== '' ? ' ' : '') . $name . ' ' . $value;
            }
        }
        return $result;
    }

    /**
     * Perform integrity check on query parts
     *
     * @throws LogicException When required query parts are missing or parts contradict each other
     */
    protected function validate(): void
    {
        if ($this->parts[self::PART_SELECT] === '') {
            throw new LogicException('SELECT statement is missing.');
        }
        if ($this->parts[self::PART_FROM] === '') {
            throw new LogicException('FROM clause is missing.');
        }
        if ($this->parts[self::PART_COMPARE_WITH] !== ''
            && $this->parts[self::PART_SINCE] === ''
            && $this->parts[self::PART_UNTIL] === ''
        ) {
            throw new LogicException('COMPARE WITH clause requires a SINCE or UNTIL clause.');
        }
    }

    /**
     * Assign UNTIL clause to define the end of a time range across which to pull data
     */
    public function until(MomentAbstract $moment): self
    {
        return $this->setPart(self::PART_UNTIL, $moment->renderNrql());
    }

    /**
     * Assign COMPARE WITH clause to compare the values for two different time ranges
     */
    public function compareWith(MomentAbstract $moment): self
    {
        return $this->setPart(self::PART_COMPARE_WITH, $moment->renderNrql());
    }

    /**
     * Assign TIMESERIES clause to return data as a time series broken out by a specified period of time
     *
     * @param TimePeriod|null $period Specified time period or automatic detection if null
     * @param string $default Default value when period is null
     */
    public function timeSeries(?TimePeriod $period = null, string $default = 'AUTO'): self
    {
        return $this->setPart(self::PART_TIME_SERIES, $period !== null ? $period->renderNrql() : $default);
    }

    /**
     * Return rendered query when instance is used in a string context.
     * Convert exceptions to PHP errors, because exceptions are prohibited for this magic method.
     */
    public function __toString(): string
    {
        try {
            return $this->renderNrql();
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return '';
        }
    }
}
