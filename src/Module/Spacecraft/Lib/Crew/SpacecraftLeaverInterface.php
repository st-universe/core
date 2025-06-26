<?php

namespace Stu\Module\Spacecraft\Lib\Crew;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignment;

interface SpacecraftLeaverInterface
{
    public function evacuate(SpacecraftWrapperInterface $wrapper): string;

    public function dumpCrewman(CrewAssignment $shipCrew, string $message): string;
}
