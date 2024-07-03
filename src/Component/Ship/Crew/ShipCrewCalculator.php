<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Crew;

use Override;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

/**
 * Provides several calculation methods to retrieve the (max) crew counts of rumps and ships
 */
final class ShipCrewCalculator implements ShipCrewCalculatorInterface
{
    public function __construct(private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository)
    {
    }

    #[Override]
    public function getMaxCrewCountByRump(
        ShipRumpInterface $shipRump
    ): int {
        if ($this->getCrewObj($shipRump) === null) {
            return $this->getBaseCrewCount($shipRump);
        } else {
            return $this->getBaseCrewCount($shipRump) + $this->getCrewObj($shipRump)->getJob6Crew();
        }
    }

    #[Override]
    public function getCrewObj(
        ShipRumpInterface $shipRump
    ): ?ShipRumpCategoryRoleCrewInterface {
        return $this->shipRumpCategoryRoleCrewRepository
            ->getByShipRumpCategoryAndRole(
                $shipRump->getCategoryId(),
                (int) $shipRump->getRoleId()
            );
    }

    #[Override]
    public function getMaxCrewCountByShip(
        ShipInterface $ship
    ): int {
        $rump = $ship->getRump();

        $crewCount = $this->getMaxCrewCountByRump($rump);

        if ($ship->isTroopQuartersHealthy()) {
            if ($rump->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE) {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT_BASE;
            } else {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT;
            }
        }
        return $crewCount;
    }

    #[Override]
    public function getCrewUsage(array $modules, ShipRumpInterface $rump, UserInterface $user): int
    {
        return array_reduce(
            $modules,
            fn (int $value, ModuleInterface $module): int => $value + $module->getCrewByFactionAndRumpLvl(
                $user->getFaction(),
                $rump
            ),
            $rump->getBaseCrew()
        );
    }

    private function getBaseCrewCount(ShipRumpInterface $shipRump): int
    {
        $count = $shipRump->getBaseCrew();
        if ($this->getCrewObj($shipRump) !== null) {
            foreach ([1, 2, 3, 4, 5, 7] as $slot) {
                $crew_func = 'getJob' . $slot . 'Crew';
                $count += $this->getCrewObj($shipRump)->$crew_func();
            }
        }
        return $count;
    }
}
