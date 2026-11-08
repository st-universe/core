<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Orm\Entity\Module;

final readonly class TechnologyOrder
{
    /** @param array<int, int> $costs */
    public function __construct(
        public array $costs,
        public ?Module $module = null,
        public ?AwayMissionResearchEnum $system = null,
        public int $chance = 0,
        public int $steps = 0
    ) {}
}
