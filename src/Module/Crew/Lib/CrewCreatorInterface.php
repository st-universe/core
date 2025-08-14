<?php

namespace Stu\Module\Crew\Lib;

use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;

interface CrewCreatorInterface
{
    public function create(int $userId, ?Colony $colony = null): CrewAssignment;

    public function createCrewAssignments(Spacecraft $spacecraft, EntityWithCrewAssignmentsInterface $crewProvider, ?int $amount = null): void;
}
