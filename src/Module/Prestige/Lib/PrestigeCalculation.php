<?php

namespace Stu\Module\Prestige\Lib;

use Override;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class PrestigeCalculation implements PrestigeCalculationInterface
{

    #[Override]
    public function getPrestigeOfSpacecraftOrFleet(SpacecraftWrapperInterface|SpacecraftInterface $spacecraft): int
    {
        $target = $spacecraft instanceof SpacecraftInterface ? $spacecraft : $spacecraft->get();

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

    #[Override]
    public function targetHasPositivePrestige(SpacecraftInterface $target): bool
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

    #[Override]
    public function getPrestigeOfBattleParty(BattlePartyInterface $battleParty): int
    {
        return $battleParty->getActiveMembers()
            ->map(fn(SpacecraftWrapperInterface $wrapper): int => $wrapper->get()->getRump()->getPrestige())
            ->reduce(
                fn(int $sum, int $prestige): int => $sum + $prestige,
                0
            );
    }
}
