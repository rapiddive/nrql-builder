<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

use InvalidArgumentException;
use LogicException;
use Rapiddive\NrqlBuilder\Moment\MomentInterface;

/**
 * Builder of a query in New Relic Query Language (NRQL) with fluent interface to set query parts in an arbitrary order
 *
 * @link https://docs.newrelic.com/docs/insights/new-relic-insights/using-new-relic-query-language/nrql-reference
 */
class QueryBuilder implements SyntaxRendererInterface
{
    const PART_SELECT = 'SELECT';
    const PART_FROM = 'FROM';
    const PART_WHERE = 'WHERE';
    const PART_FACET = 'FACET';
    const PART_LIMIT = 'LIMIT';
    const PART_SINCE = 'SINCE';
    const PART_UNTIL = 'UNTIL';
    const PART_COMPARE_WITH = 'COMPARE WITH';
    const PART_TIME_SERIES = 'TIMESERIES';
    const PART_WITH_TIMEZONE = 'WITH TIMEZONE';

    /** @var array<string,string|int> */
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
        self::PART_WITH_TIMEZONE => '',
    ];

    public function withTimeZone(?string $timezone = null): static
    {
        return $this->setPart(self::PART_WITH_TIMEZONE, $timezone !== null ? "'$timezone'" : '');
    }

    /**
     * Clear a previously assigned query part, allowing it to be set again.
     *
     * @throws InvalidArgumentException When the part name is not recognised
     */
    public function resetPart(string $part): static
    {
        if (!array_key_exists($part, $this->parts)) {
            throw new InvalidArgumentException("Query part '$part' is not recognized.");
        }
        $this->parts[$part] = '';
        return $this;
    }

    protected function setPart(string $part, string|int $value): static
    {
        if (!array_key_exists($part, $this->parts)) {
            throw new InvalidArgumentException("Query part '$part' is not recognized.");
        }
        if (!empty($this->parts[$part])) {
            throw new InvalidArgumentException("Value has already been assigned to the query part '$part'.");
        }
        $this->parts[$part] = $value;
        return $this;
    }

    public function select(array $attributes): static
    {
        return $this->setPart(self::PART_SELECT, implode(', ', $attributes));
    }

    public function selectAll(): static
    {
        return $this->setPart(self::PART_SELECT, '*');
    }

    public function from(array $events): static
    {
        return $this->setPart(self::PART_FROM, implode(', ', $events));
    }

    public function where(string $conditions): static
    {
        return $this->setPart(self::PART_WHERE, $conditions);
    }

    public function facet(string $attribute): static
    {
        return $this->setPart(self::PART_FACET, $attribute);
    }

    /** @throws InvalidArgumentException When $count is less than 1 */
    public function limit(int $count): static
    {
        if ($count < 1) {
            throw new InvalidArgumentException('LIMIT must be a positive integer.');
        }
        return $this->setPart(self::PART_LIMIT, $count);
    }

    public function since(MomentInterface $moment): static
    {
        return $this->setPart(self::PART_SINCE, $moment->renderNrql());
    }

    public function until(MomentInterface $moment): static
    {
        return $this->setPart(self::PART_UNTIL, $moment->renderNrql());
    }

    public function compareWith(MomentInterface $moment): static
    {
        return $this->setPart(self::PART_COMPARE_WITH, $moment->renderNrql());
    }

    public function timeSeries(?TimePeriod $period = null, string $default = 'AUTO'): static
    {
        return $this->setPart(self::PART_TIME_SERIES, $period ? $period->renderNrql() : $default);
    }

    /** {@inheritdoc} */
    public function renderNrql(): string
    {
        $this->validate($this->parts);
        $result = '';
        foreach ($this->parts as $name => $value) {
            if ($value) {
                $result .= ($result ? ' ' : '') . $name . ' ' . $value;
            }
        }
        return $result;
    }

    public function __toString(): string
    {
        return $this->renderNrql();
    }

    /** @throws LogicException When required parts are missing or contradict each other */
    protected function validate(array $parts): void
    {
        if (empty($parts[self::PART_SELECT])) {
            throw new LogicException('SELECT statement is missing.');
        }
        if (empty($parts[self::PART_FROM])) {
            throw new LogicException('FROM clause is missing.');
        }
        if (!empty($parts[self::PART_COMPARE_WITH])
            && empty($parts[self::PART_SINCE])
            && empty($parts[self::PART_UNTIL])
        ) {
            throw new LogicException('COMPARE WITH clause requires a SINCE or UNTIL clause.');
        }
    }
}
