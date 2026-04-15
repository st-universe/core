<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    public function __construct(
        private readonly ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private readonly ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private readonly ColonyFunctionManagerInterface $colonyFunctionManager,
        private readonly RepairUtilInterface $repairUtil,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository
    ) {}

    #[\Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony,
            $buildingFunction
        );
    }

    #[\Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host, ?PlanetField $field = null): void
    {
        if ($host instanceof Colony) {
            $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);

            if ($buildingFunction === BuildingFunctionEnum::REPAIR_SHIPYARD) {
                $activeSlotCount = 1;
                if (
                    $field !== null
                    && $this->colonyFunctionManager->hasActiveFunction(
                        $host,
                        BuildingFunctionEnum::REPAIR_SHIPYARD,
                        false,
                        [$field->getFieldId()]
                    )
                ) {
                    $activeSlotCount = 2;
                }

                $this->refreshRepairQueuesOnColony($host, $activeSlotCount);
                return;
            }

            if ($field !== null) {
                $this->pauseRepairQueue($host, $field);
            }
        }
    }

    #[\Override]
    public function activate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host, ?PlanetField $field = null): void
    {
        if ($host instanceof Colony) {
            $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);

            if ($buildingFunction === BuildingFunctionEnum::REPAIR_SHIPYARD) {
                // Activation of repair station always enables the second active repair slot immediately.
                $this->refreshRepairQueuesOnColony($host, 2);
                return;
            }

            if ($field !== null) {
                $this->resumeRepairQueue($host, $field);
            }
        }
    }

    private function pauseRepairQueue(Colony $colony, PlanetField $field): void
    {
        $this->pauseRepairJobs(
            $this->colonyShipRepairRepository->getByColonyField($colony->getId(), $field->getFieldId()),
            time()
        );
    }

    private function resumeRepairQueue(Colony $colony, PlanetField $field): void
    {
        $jobs = $this->colonyShipRepairRepository->getByColonyField($colony->getId(), $field->getFieldId());
        if ($jobs === []) {
            return;
        }

        $time = time();
        $activeSlotCount = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ? 2
            : 1;

        $this->resumeRepairJobs($jobs, $activeSlotCount, $time);
    }

    private function refreshRepairQueuesOnColony(Colony $colony, int $activeSlotCount): void
    {
        $queuesByField = [];
        foreach ($this->colonyShipRepairRepository->getAllOrdered() as $job) {
            if ($job->getColonyId() !== $colony->getId()) {
                continue;
            }

            $queuesByField[$job->getFieldId()][] = $job;
        }

        if ($queuesByField === []) {
            return;
        }

        $time = time();

        foreach ($queuesByField as $fieldId => $jobs) {
            $this->pauseRepairJobs($jobs, $time);

            $field = $this->planetFieldRepository->getByColonyAndFieldIndex($colony->getId(), (int) $fieldId);
            if ($field === null || !$field->isActive()) {
                continue;
            }

            $this->resumeRepairJobs($jobs, $activeSlotCount, $time);
        }
    }

    /**
     * @param array<ColonyShipRepair> $jobs
     */
    private function pauseRepairJobs(array $jobs, int $time): void
    {
        foreach ($jobs as $job) {
            if ($job->getFinishTime() > 0 && $job->getStopDate() === 0 && !$job->isStopped()) {
                $job->setStopDate($time);
            }
        }
    }

    /**
     * @param array<ColonyShipRepair> $jobs
     */
    private function resumeRepairJobs(array $jobs, int $activeSlotCount, int $time): void
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
                $job->setFinishTime($time + $this->repairUtil->getPassiveRepairStepDuration($job->getShip()));
            }
        }
    }
}
