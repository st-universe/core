<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Override;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

/**
 * Provides several calculation methods to retrieve the (max) crew counts of rumps and ships
 */
final class SpacecraftCrewCalculator implements SpacecraftCrewCalculatorInterface
{
    public function __construct(private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository) {}

    #[Override]
    public function getMaxCrewCountByRump(
        SpacecraftRump $shipRump
    ): int {
        if ($this->getCrewObj($shipRump) === null) {
            return $this->getBaseCrewCount($shipRump);
        } else {
            return $this->getBaseCrewCount($shipRump) + $this->getCrewObj($shipRump)->getJob6Crew();
        }
    }

    #[Override]
    public function getCrewObj(
        SpacecraftRump $shipRump
    ): ?ShipRumpCategoryRoleCrew {

        $roleId = $shipRump->getRoleId();
        if ($roleId === null) {
            return null;
        }

        return $this->shipRumpCategoryRoleCrewRepository
            ->getByShipRumpCategoryAndRole(
                $shipRump->getCategoryId(),
                $roleId
            );
    }

    #[Override]
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

    #[Override]
    public function getCrewUsage(array $modules, SpacecraftRump $rump, User $user): int
    {
        return array_reduce(
            $modules,
            fn(int $value, Module $module): int => $value + $module->getCrewByFactionAndRumpLvl(
                $user->getFaction(),
                $rump
            ),
            $rump->getBaseValues()->getBaseCrew()
        );
    }

    private function getBaseCrewCount(SpacecraftRump $shipRump): int
    {
        $count = $shipRump->getBaseValues()->getBaseCrew();
        if ($this->getCrewObj($shipRump) !== null) {
            foreach ([1, 2, 3, 4, 5, 7] as $slot) {
                $crew_func = 'getJob' . $slot . 'Crew';
                $count += $this->getCrewObj($shipRump)->$crew_func();
            }
        }
        return $count;
    }
}
