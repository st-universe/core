<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Override;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

/**
 * Provides several calculation methods to retrieve the (max) crew counts of rumps and ships
 */
final class SpacecraftCrewCalculator implements SpacecraftCrewCalculatorInterface
{
    public function __construct(private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository) {}

    #[Override]
    public function getMaxCrewCountByRump(
        SpacecraftRumpInterface $shipRump
    ): int {
        if ($this->getCrewObj($shipRump) === null) {
            return $this->getBaseCrewCount($shipRump);
        } else {
            return $this->getBaseCrewCount($shipRump) + $this->getCrewObj($shipRump)->getJob6Crew();
        }
    }

    #[Override]
    public function getCrewObj(
        SpacecraftRumpInterface $shipRump
    ): ?ShipRumpCategoryRoleCrewInterface {

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
        SpacecraftInterface $spacecraft
    ): int {
        $rump = $spacecraft->getRump();

        $crewCount = $this->getMaxCrewCountByRump($rump);

        if ($spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::TROOP_QUARTERS)) {
            if ($rump->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_BASE) {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT_BASE;
            } else {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT;
            }
        }
        return $crewCount;
    }

    #[Override]
    public function getCrewUsage(array $modules, SpacecraftRumpInterface $rump, UserInterface $user): int
    {
        return array_reduce(
            $modules,
            fn(int $value, ModuleInterface $module): int => $value + $module->getCrewByFactionAndRumpLvl(
                $user->getFaction(),
                $rump
            ),
            $rump->getBaseValues()->getBaseCrew()
        );
    }

    private function getBaseCrewCount(SpacecraftRumpInterface $shipRump): int
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
