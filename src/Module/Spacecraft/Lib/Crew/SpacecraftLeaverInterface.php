<?php

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;

interface SpacecraftLeaverInterface
{
    public function evacuate(SpacecraftWrapperInterface $wrapper): string;

    public function dumpCrewman(CrewAssignmentInterface $shipCrew, string $message): string;
}
