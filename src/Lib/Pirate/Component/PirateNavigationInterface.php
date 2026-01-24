<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\StarSystem;

interface PirateNavigationInterface
{
    public function navigateToTarget(
        PirateFleetBattleParty $pirateFleetBattleParty,
        Location|StarSystem $target
    ): bool;
}
