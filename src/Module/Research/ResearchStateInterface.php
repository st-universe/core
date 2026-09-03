<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\Researched;

interface ResearchStateInterface
{
    public function finish(Researched $state): void;

    /** returns the remaining amount */
    public function advance(Researched $state, int $amount): int;
}
