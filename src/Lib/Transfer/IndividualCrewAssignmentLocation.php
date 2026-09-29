<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\CrewAssignment;

final class IndividualCrewAssignmentLocation
{
    public function __construct(
        public readonly CrewAssignment $assignment,
        public readonly int $side,
        public readonly CrewTypeEnum $slot
    ) {}
}
