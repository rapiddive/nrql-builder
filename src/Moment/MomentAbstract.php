<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

use Rapiddive\NrqlBuilder\SyntaxRendererInterface;

/**
 * Moment in time that can be expressed in NRQL syntax
 */
abstract class MomentAbstract implements SyntaxRendererInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function renderNrql(): string;
}
