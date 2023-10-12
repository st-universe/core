<?php

namespace Stu\Module\Ship\Lib\CloseCombat;

use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\ShipInterface;

interface CloseCombatUtilInterface
{
    /** 
     * Returns a maximum of 5 Crewman with the highest fight capabilities.
     * 
     * @return array<int, ShipCrewInterface>  
     */
    public function getCombatGroup(ShipInterface $ship): array;

    /** @param array<ShipCrewInterface> $combatGroup */
    public function getCombatValue(array $combatGroup, FactionInterface $faction): int;
}
