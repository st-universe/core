<?php

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface TroopTransferUtilityInterface
{
    public function getFreeQuarters(Spacecraft $ship): int;

    public function getBeamableTroopCount(Spacecraft $spacecraft): int;

    public function ownCrewOnTarget(User $user, EntityWithCrewAssignmentsInterface $target): int;

    public function foreignerCount(Spacecraft $spacecraft): int;

    public function assignCrew(CrewAssignment $crewAssignment, EntityWithCrewAssignmentsInterface $target): void;
}
