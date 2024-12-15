<?php

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface CloseCombatUtilInterface
{
    /**
     * Returns a maximum of 5 Crewman with the highest fight capabilities.
     *
     * @return array<int, CrewAssignmentInterface>
     */
    public function getCombatGroup(SpacecraftInterface $spacecraft): array;

    /** @param array<CrewAssignmentInterface> $combatGroup */
    public function getCombatValue(array $combatGroup, FactionInterface $faction): int;
}
