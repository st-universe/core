<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ReloadMinimalEps implements ReloadMinimalEpsInterface
{
    #[\Override]
    public function reload(PirateFleetBattleParty $pirateFleetBattleParty, int $minimalPercentage = 20): void
    {
        foreach ($pirateFleetBattleParty->getActiveMembers() as $wrapper) {
            $this->reloadEps($wrapper, $minimalPercentage);
            $this->reloadWarpdrive($wrapper, $minimalPercentage);
        }
    }

    private function reloadEps(SpacecraftWrapperInterface $wrapper, int $minimalPercentage): void
    {
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $epsSystem !== null
            && $epsSystem->getEpsPercentage() < $minimalPercentage
        ) {
            $epsSystem->setEps((int)($epsSystem->getMaxEps() * $minimalPercentage / 100))->update();
        }
    }

    private function reloadWarpdrive(SpacecraftWrapperInterface $wrapper, int $minimalPercentage): void
    {
        $warpdrive = $wrapper->getWarpDriveSystemData();

        if (
            $warpdrive !== null
            && $warpdrive->getWarpdrivePercentage() < $minimalPercentage
        ) {
            $warpdrive->setWarpDrive((int)($warpdrive->getMaxWarpdrive() * $minimalPercentage / 100))->update();
        }
    }
}
