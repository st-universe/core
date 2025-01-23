<?php

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface TroopTransferUtilityInterface
{
    public function getFreeQuarters(SpacecraftInterface $ship): int;

    public function getBeamableTroopCount(SpacecraftInterface $spacecraft): int;

    public function ownCrewOnTarget(UserInterface $user, EntityWithCrewAssignmentsInterface $target): int;

    public function foreignerCount(SpacecraftInterface $spacecraft): int;

    public function assignCrew(CrewAssignmentInterface $crewAssignment, EntityWithCrewAssignmentsInterface $target): void;
}
