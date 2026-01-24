<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;

interface ReloadMinimalEpsInterface
{
    public function reload(PirateFleetBattleParty $pirateFleetBattleParty, int $minimalPercentage = 20): void;
}
