<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Ship;

interface PirateAttackInterface
{
    public function attackShip(PirateFleetBattleParty $pirateFleetBattleParty, Ship $target): void;
}
