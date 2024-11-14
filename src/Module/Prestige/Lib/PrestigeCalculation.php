<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class PrestigeCalculation implements PrestigeCalculationInterface
{

    public function getPrestigeOfSpacecraftOrFleet(ShipWrapperInterface|ShipInterface $spacecraft): int
    {
        $target = $spacecraft instanceof ShipInterface ? $spacecraft : $spacecraft->get();

        $fleet = $target->getFleet();
        if ($fleet !== null) {
            return array_reduce(
                $fleet->getShips()->toArray(),
                fn(int $value, ShipInterface $fleetShip): int => $value + $fleetShip->getRump()->getPrestige(),
                0
            );
        }

        return $target->getRump()->getPrestige();
    }

    public function targetHasPositivePrestige(ShipInterface $target): bool
    {
        $fleet = $target->getFleet();
        if ($fleet !== null) {
            foreach ($fleet->getShips() as $ship) {
                if ($ship->getRump()->getPrestige() > 0) {
                    return true;
                }
            }
        }

        return $target->getRump()->getPrestige() > 0;
    }

    public function getPrestigeOfBattleParty(BattlePartyInterface $battleParty): int
    {
        return $battleParty->getActiveMembers()
            ->map(fn(ShipWrapperInterface $wrapper): int => $wrapper->get()->getRump()->getPrestige())
            ->reduce(
                fn(int $sum, int $prestige): int => $sum + $prestige,
                0
            );
    }
}
