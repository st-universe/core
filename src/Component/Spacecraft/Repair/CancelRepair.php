<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class CancelRepair implements CancelRepairInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private RepairTaskRepositoryInterface $repairTaskRepository,
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository
    ) {}


    #[Override]
    public function cancelRepair(Spacecraft $ship): bool
    {
        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::REPAIR_PASSIVE) {
            $this->setStateNoneAndSave($ship);

            $this->colonyShipRepairRepository->truncateByShipId($ship->getId());
            $this->stationShipRepairRepository->truncateByShipId($ship->getId());

            return true;
        } elseif ($state === SpacecraftStateEnum::REPAIR_ACTIVE) {
            $this->setStateNoneAndSave($ship);

            $this->repairTaskRepository->truncateByShipId($ship->getId());

            return true;
        }

        return false;
    }

    private function setStateNoneAndSave(Spacecraft $ship): void
    {
        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
        $this->spacecraftRepository->save($ship);
    }
}
