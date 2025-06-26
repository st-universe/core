<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

interface PrestigeCalculationInterface
{
    public function getPrestigeOfSpacecraftOrFleet(SpacecraftWrapperInterface|Spacecraft $spacecraft): int;

    public function targetHasPositivePrestige(Spacecraft $target): bool;

    public function getPrestigeOfBattleParty(BattlePartyInterface $battleParty): int;
}
