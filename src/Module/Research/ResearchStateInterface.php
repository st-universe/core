<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchedInterface;

interface ResearchStateInterface
{
    public function finish(ResearchedInterface $state): void;

    public function advance(ResearchedInterface $state, int $amount): void;
}
