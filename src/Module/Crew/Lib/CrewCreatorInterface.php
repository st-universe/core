<?php

namespace Stu\Module\Crew\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface CrewCreatorInterface
{
    public function create(UserInterface $user, ?ColonyInterface $colony = null): CrewAssignmentInterface;

    public function createCrewAssignments(SpacecraftInterface $spacecraft, ColonyInterface|SpacecraftInterface $crewProvider, ?int $amount = null): void;
}
