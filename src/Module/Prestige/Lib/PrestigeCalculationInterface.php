<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface PrestigeCalculationInterface
{
    public function getPrestigeOfSpacecraftOrFleet(ShipWrapperInterface|ShipInterface $spacecraft): int;

    public function targetHasPositivePrestige(ShipInterface $target): bool;

    public function getPrestigeOfBattleParty(BattlePartyInterface $battleParty): int;
}
