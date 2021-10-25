<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Selfrepair;

use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class SelfrepairUtil implements SelfrepairUtilInterface
{

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private RepairTaskRepositoryInterface $repairTaskRepository;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        RepairTaskRepositoryInterface $repairTaskRepository
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->repairTaskRepository = $repairTaskRepository;
    }

    public function determineFreeEngineerCount(ShipInterface $ship): int
    {
        $engineerCount = 0;

        $engineerOptions = [];
        $nextNumber = 1;
        foreach ($ship->getCrewlist() as $shipCrew) {
            if (
                $shipCrew->getSlot() === CrewEnum::CREW_TYPE_TECHNICAL
                //&& $shipCrew->getRepairTask() === null
            ) {
                $engineerOptions[] = $nextNumber;
                $nextNumber++;
                $engineerCount++;
            }
        }

        return $engineerCount; //$engineerOptions;
    }

    public function determineRepairOptions(ShipInterface $ship): array
    {
        $repairOptions = [];

        //check for hull option
        $hullPercentage = (int) $ship->getHuell() * 100 / $ship->getMaxHuell();
        if ($hullPercentage < RepairTaskEnum::BOTH_MAX) {
            $hullSystem = $this->shipSystemRepository->prototype();
            $hullSystem->setSystemType(ShipSystemTypeEnum::SYSTEM_HULL);

            $repairOptions[ShipSystemTypeEnum::SYSTEM_HULL] = $hullSystem;
        }

        //check for system options
        foreach ($ship->getDamagedSystems() as $system) {
            if ($system->getStatus() < RepairTaskEnum::BOTH_MAX) {
                $repairOptions[$system->getSystemType()] = $system;
            }
        }

        return $repairOptions;
    }

    public function createRepairTask(ShipInterface $ship, int $systemType, int $repairType, int $finishTime): void
    {
        $obj = $this->repairTaskRepository->prototype();

        $obj->setUser($ship->getUser());
        $obj->setShip($ship);
        $obj->setSystemType($systemType);
        $obj->setHealingPercentage($this->determineHealingPercentage($repairType));
        $obj->setFinishTime($finishTime);

        $this->repairTaskRepository->save($obj);
    }

    private function determineHealingPercentage(int $repairType): int
    {
        $percentage = 0;

        if ($repairType === RepairTaskEnum::SPARE_PARTS_ONLY) {
            $percentage += rand(RepairTaskEnum::SPARE_PARTS_ONLY_MIN, RepairTaskEnum::SPARE_PARTS_ONLY_MAX);
        } else if ($repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY) {
            $percentage += rand(RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MIN, RepairTaskEnum::SYSTEM_COMPONENTS_ONLY_MAX);
        } else if ($repairType === RepairTaskEnum::BOTH) {
            $percentage += rand(RepairTaskEnum::BOTH_MIN, RepairTaskEnum::BOTH_MAX);
        }

        return $percentage;
    }

    public function instantSelfRepair($ship, $systemType, $repairType): void
    {
        $this->internalSelfRepair($ship, $systemType, $this->determineHealingPercentage($repairType));
    }

    public function selfRepair(ShipInterface $ship, RepairTaskInterface $repairTask): void
    {
        $systemType = $repairTask->getSystemType();
        $percentage = $repairTask->getHealingPercentage();

        $this->internalSelfRepair($ship, $systemType, $percentage);
        $this->repairTaskRepository->delete($repairTask);
    }

    private function internalSelfRepair(ShipInterface $ship, int $systemType, int $percentage): void
    {
        if ($systemType === ShipSystemTypeEnum::SYSTEM_HULL) {
            $ship->setHuell((int)($ship->getMaxHuell() * $percentage / 100));
        } else {
            $system = $ship->getShipSystem($systemType);
            $system->setStatus($percentage);

            $this->shipSystemRepository->save($system);
        }

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
    }
}
