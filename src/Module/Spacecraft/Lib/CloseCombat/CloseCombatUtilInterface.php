<?php

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;

interface CloseCombatUtilInterface
{
    /**
     * Returns a maximum of 5 Crewman with the highest fight capabilities.
     *
     * @return array<int, CrewAssignment>
     */
    public function getCombatGroup(Spacecraft $spacecraft): array;

    /** @param array<CrewAssignment> $combatGroup */
    public function getCombatValue(array $combatGroup, Faction $faction): int;
}
