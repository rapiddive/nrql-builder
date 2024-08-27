<?php
declare(strict_types=1);

namespace Rapiddive\NrqlBuilder\Moment;

/**
 * Relative moment one day in the past
 */
class Yesterday extends MomentAbstract
{
    /**
     * {@inheritdoc}
     */
    public function renderNrql(): string
    {
        return 'YESTERDAY';
    }
}
