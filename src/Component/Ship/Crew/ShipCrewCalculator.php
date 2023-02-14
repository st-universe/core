<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Crew;

use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

/**
 * Provides several calculation methods to retrieve the (max) crew counts of rumps and ships
 */
final class ShipCrewCalculator implements ShipCrewCalculatorInterface
{
    private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository;

    public function __construct(
        ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository
    ) {
        $this->shipRumpCategoryRoleCrewRepository = $shipRumpCategoryRoleCrewRepository;
    }

    public function getMaxCrewCountByRump(
        ShipRumpInterface $shipRump
    ): int {
        if ($this->getCrewObj($shipRump) === null) {
            return $this->getBaseCrewCount($shipRump);
        } else {
            return $this->getBaseCrewCount($shipRump) + $this->getCrewObj($shipRump)->getJob6Crew();
        }
    }

    public function getCrewObj(
        ShipRumpInterface $shipRump
    ): ?ShipRumpCategoryRoleCrewInterface {
        return $this->shipRumpCategoryRoleCrewRepository
            ->getByShipRumpCategoryAndRole(
                 $shipRump->getCategoryId(),
                (int) $shipRump->getRoleId()
            );
    }

    public function getMaxCrewCountByShip(
        ShipInterface $ship
    ): int {
        $rump = $ship->getRump();

        $crewCount = $this->getMaxCrewCountByRump($rump);

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            if ($rump->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE) {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT_BASE;
            } else {
                $crewCount += TroopQuartersShipSystem::QUARTER_COUNT;
            }
        }
        return $crewCount;
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