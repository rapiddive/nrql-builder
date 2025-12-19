<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder;

/**
 * Anything that has representation in NRQL syntax
 */
interface SyntaxRendererInterface
{
    /**
     * Return representation in NRQL syntax
     */
    public function renderNrql(): string;
}
