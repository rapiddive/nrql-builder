<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;


/**
 * Moment in time that can be expressed in NRQL syntax
 */
abstract class MomentAbstract implements MomentInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function renderNrql(): string;
}
