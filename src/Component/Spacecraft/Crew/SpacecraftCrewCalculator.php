<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;

/**
 * Provides several calculation methods to retrieve the (max) crew counts of rumps and ships
 */
final class SpacecraftCrewCalculator implements SpacecraftCrewCalculatorInterface
{
    #[\Override]
    public function getMaxCrewCountByRump(
        SpacecraftRump $shipRump
    ): int {
        return $shipRump->getBaseValues()->getMaxCrew();
    }

    #[\Override]
    public function getMaxCrewCountByShip(
        Spacecraft $spacecraft
    ): int {
        $rump = $spacecraft->getRump();

        $crewCount = $this->getMaxCrewCountByRump($rump);

        if ($spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::TROOP_QUARTERS)) {
            if ($rump->getRoleId() === SpacecraftRumpRoleEnum::BASE) {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT_BASE;
            } else {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT;
            }
        }
        return $crewCount;
    }

    #[\Override]
    public function getCrewUsage(array $modules, SpacecraftRump $rump, User $user): int
    {
        return array_reduce(
            $modules,
            fn (int $value, Module $module): int => $value + $module->getCrewByFactionAndRumpLvl(
                $user->getFaction(),
                $rump
            ),
            $rump->getBaseValues()->getBaseCrew()
        );
    }
}
