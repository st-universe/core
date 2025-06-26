<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Control\StuRandom;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipsDetectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Ship;

class TrapDetection implements TrapDetectionInterface
{
    public function __construct(
        private AlertedShipsDetectionInterface $alertedShipsDetection,
        private PrestigeCalculationInterface $prestigeCalculation,
        private StuRandom $stuRandom
    ) {}

    #[Override]
    public function isAlertTrap(Location $location, Ship $leadShip): bool
    {
        $alertedWrappers = $this->alertedShipsDetection->getAlertedShipsOnLocation(
            $location,
            $leadShip->getUser()
        );
        if ($alertedWrappers->isEmpty()) {
            return false;
        }

        $piratePrestige = $this->prestigeCalculation->getPrestigeOfSpacecraftOrFleet($leadShip);
        $alertedPrestige = $this->getPrestigeOfAlertedSpacecrafts($alertedWrappers);

        if ($alertedPrestige <= 3 * $piratePrestige) {
            return false;
        }

        return $this->stuRandom->rand(0, $alertedPrestige) > $piratePrestige;
    }

    /** @param Collection<int, SpacecraftWrapperInterface> $alertedWrappers */
    private function getPrestigeOfAlertedSpacecrafts(Collection $alertedWrappers): int
    {
        return $alertedWrappers
            ->map(fn(SpacecraftWrapperInterface $wrapper): int => $this->prestigeCalculation->getPrestigeOfSpacecraftOrFleet($wrapper))
            ->reduce(
                fn(int $sum, int $prestige): int => $sum + $prestige,
                0
            );
    }
}
