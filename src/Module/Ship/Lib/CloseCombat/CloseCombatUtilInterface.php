<?php

namespace Stu\Module\Ship\Lib\CloseCombat;

use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\ShipInterface;

interface CloseCombatUtilInterface
{
    /** 
     * Returns a maximum of 5 Crewman with the highest fight capabilities.
     * 
     * @return array<int, CrewInterface>  
     */
    public function getCombatGroup(ShipInterface $ship): array;

    /** @param array<CrewInterface> $combatGroup */
    public function getCombatValue(array $combatGroup, FactionInterface $faction): int;
}
