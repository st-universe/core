<?php

namespace Stu\Module\Crew\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface CrewCreatorInterface
{
    public function create(int $userId, ?ColonyInterface $colony = null): CrewAssignmentInterface;

    public function createCrewAssignment(SpacecraftInterface $spacecraft, ColonyInterface|SpacecraftInterface $crewProvider, ?int $amount = null): void;
}
