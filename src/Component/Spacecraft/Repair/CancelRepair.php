<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class CancelRepair implements CancelRepairInterface
{
    public function __construct(
        private RepairTaskRepositoryInterface $repairTaskRepository,
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository
    ) {}


    #[\Override]
    public function cancelRepair(Spacecraft $ship): bool
    {
        return $this->cancelRepairWithResult($ship)->isCancelled();
    }

    #[\Override]
    public function cancelRepairWithResult(Spacecraft $ship): CancelRepairResult
    {
        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::REPAIR_PASSIVE) {
            $colonyRepairJob = $this->colonyShipRepairRepository->getByShip($ship->getId());
            $stationRepairJob = $this->stationShipRepairRepository->getByShip($ship->getId());

            $this->setStateNoneAndSave($ship);

            if ($colonyRepairJob !== null) {
                $this->colonyShipRepairRepository->delete($colonyRepairJob);
                $this->refreshColonyQueueAfterRemoval($colonyRepairJob->getColony(), $colonyRepairJob->getFieldId());
            }
            if ($stationRepairJob !== null) {
                $this->stationShipRepairRepository->delete($stationRepairJob);
                $this->refreshStationQueueAfterRemoval($stationRepairJob->getStation());
            }

            return new CancelRepairResult(true);
        } elseif ($state === SpacecraftStateEnum::REPAIR_ACTIVE) {
            $shipId = $ship->getId();
            $repairTask = $this->repairTaskRepository->getByShip($shipId);
            [$refundedSpareParts, $refundedSystemComponents] = $repairTask === null
                ? [0, 0]
                : $this->refundActiveRepairCommodities($ship, $repairTask);

            $this->setStateNoneAndSave($ship);

            $this->repairTaskRepository->truncateByShipId($shipId);

            return new CancelRepairResult(true, $refundedSpareParts, $refundedSystemComponents);
        }

        return new CancelRepairResult(false);
    }

    private function setStateNoneAndSave(Spacecraft $ship): void
    {
        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
    }

    /** @return array{0: int, 1: int} */
    private function refundActiveRepairCommodities(Spacecraft $ship, RepairTask $repairTask): array
    {
        $repairType = $this->determineRepairType($repairTask->getHealingPercentage());
        if ($repairType === null) {
            return [0, 0];
        }

        $neededPartCount = (int) ($ship->getMaxHull() / 150);
        $refundedSpareParts = 0;
        $refundedSystemComponents = 0;

        if ($repairType === RepairTaskConstants::SPARE_PARTS_ONLY || $repairType === RepairTaskConstants::BOTH) {
            $refundedSpareParts = $this->refundCommodity(
                $ship,
                CommodityTypeConstants::COMMODITY_SPARE_PART,
                $neededPartCount
            );
        }

        if ($repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY || $repairType === RepairTaskConstants::BOTH) {
            $refundedSystemComponents = $this->refundCommodity(
                $ship,
                CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT,
                $neededPartCount
            );
        }

        return [$refundedSpareParts, $refundedSystemComponents];
    }

    private function determineRepairType(int $healingPercentage): ?int
    {
        if (
            $healingPercentage >= RepairTaskConstants::SPARE_PARTS_ONLY_MIN
            && $healingPercentage <= RepairTaskConstants::SPARE_PARTS_ONLY_MAX
        ) {
            return RepairTaskConstants::SPARE_PARTS_ONLY;
        }

        if (
            $healingPercentage >= RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MIN
            && $healingPercentage <= RepairTaskConstants::SYSTEM_COMPONENTS_ONLY_MAX
        ) {
            return RepairTaskConstants::SYSTEM_COMPONENTS_ONLY;
        }

        if (
            $healingPercentage >= RepairTaskConstants::BOTH_MIN
            && $healingPercentage <= RepairTaskConstants::BOTH_MAX
        ) {
            return RepairTaskConstants::BOTH;
        }

        return null;
    }

    private function refundCommodity(Spacecraft $ship, int $commodityId, int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        $commodity = $this->commodityRepository->find($commodityId);
        if (!$commodity instanceof Commodity) {
            return 0;
        }

        $this->storageManager->upperStorage($ship, $commodity, $amount);

        return $amount;
    }

    private function refreshColonyQueueAfterRemoval(Colony $colony, int $fieldId): void
    {
        $jobs = $this->colonyShipRepairRepository->getByColonyField($colony->getId(), $fieldId);
        if ($jobs === [] || $this->isStoppedScope($jobs)) {
            return;
        }

        $time = time();
        $field = $this->planetFieldRepository->getByColonyAndFieldIndex($colony->getId(), $fieldId);

        if ($field === null || !$field->isActive() || $colony->isBlocked()) {
            $this->pauseJobs($jobs, $time);
            return;
        }

        $activeSlotCount = $this->colonyFunctionManager->hasActiveFunction(
            $colony,
            BuildingFunctionEnum::REPAIR_SHIPYARD,
            false
        ) ? 2 : 1;

        $this->resumeJobs($jobs, $activeSlotCount, $time);
    }

    private function refreshStationQueueAfterRemoval(Station $station): void
    {
        $jobs = $this->stationShipRepairRepository->getByStation($station->getId());
        if ($jobs === [] || $this->isStoppedScope($jobs)) {
            return;
        }

        $time = time();
        if (!$this->canRepairShips($station)) {
            $this->pauseJobs($jobs, $time);
            return;
        }

        $this->resumeJobs($jobs, 1, $time);
    }

    private function canRepairShips(Station $station): bool
    {
        $roleId = $station->getRump()->getShipRumpRole()?->getId();

        return (
            $roleId === SpacecraftRumpRoleEnum::SHIPYARD
            || $roleId === SpacecraftRumpRoleEnum::BASE
        ) && $station->hasEnoughCrew();
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function isStoppedScope(array $jobs): bool
    {
        foreach ($jobs as $job) {
            if ($job->isStopped()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function pauseJobs(array $jobs, int $time): void
    {
        foreach ($jobs as $job) {
            if ($job->getFinishTime() > 0 && $job->getStopDate() === 0 && !$job->isStopped()) {
                $job->setStopDate($time);
            }
        }
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function resumeJobs(array $jobs, int $activeSlotCount, int $time): void
    {
        foreach ($jobs as $index => $job) {
            if ($index >= $activeSlotCount || $job->isStopped()) {
                continue;
            }

            if ($job->getStopDate() > 0 && $job->getFinishTime() > 0) {
                $job->setFinishTime($job->getFinishTime() + ($time - $job->getStopDate()));
                $job->setStopDate(0);
                continue;
            }

            if ($job->getFinishTime() === 0 && $job->getStopDate() === 0) {
                $job->setFinishTime($time + $this->getPassiveRepairStepDuration($job->getShip()));
            }
        }
    }

    private function getPassiveRepairStepDuration(Ship $ship): int
    {
        return max(60, (int)ceil($ship->getRump()->getBuildtime() / 2));
    }
}
