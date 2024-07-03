<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Repair;

use Override;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class CancelRepair implements CancelRepairInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private RepairTaskRepositoryInterface $repairTaskRepository, private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository, private StationShipRepairRepositoryInterface $stationShipRepairRepository)
    {
    }


    #[Override]
    public function cancelRepair(ShipInterface $ship): bool
    {
        $state = $ship->getState();
        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $this->setStateNoneAndSave($ship);

            $this->colonyShipRepairRepository->truncateByShipId($ship->getId());
            $this->stationShipRepairRepository->truncateByShipId($ship->getId());

            return true;
        } elseif ($state === ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE) {
            $this->setStateNoneAndSave($ship);

            $this->repairTaskRepository->truncateByShipId($ship->getId());

            return true;
        }

        return false;
    }

    private function setStateNoneAndSave(ShipInterface $ship): void
    {
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $this->shipRepository->save($ship);
    }
}
