<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface PrestigeCalculationInterface
{
    public function getPrestigeOfSpacecraftOrFleet(SpacecraftWrapperInterface|SpacecraftInterface $spacecraft): int;

    public function targetHasPositivePrestige(SpacecraftInterface $target): bool;

    public function getPrestigeOfBattleParty(BattlePartyInterface $battleParty): int;
}
