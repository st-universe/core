<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Repair\RepairUtil;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class FinishPassiveShipRepairJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private readonly ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private readonly StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyFunctionManagerInterface $colonyFunctionManager,
        private readonly StationUtilityInterface $stationUtility,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly RepairUtilInterface $repairUtil,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function work(): void
    {
        $this->processColonyRepairs();
        $this->processStationRepairs();
    }

    private function processColonyRepairs(): void
    {
        /** @var array<string, array<ColonyShipRepair>> $queues */
        $queues = [];
        foreach ($this->colonyShipRepairRepository->getAllOrdered() as $repair) {
            $key = sprintf('%d_%d', $repair->getColonyId(), $repair->getFieldId());
            $queues[$key][] = $repair;
        }

        foreach ($queues as $jobs) {
            $this->processColonyQueue($jobs);
        }
    }

    /**
     * @param array<ColonyShipRepair> $jobs
     */
    private function processColonyQueue(array $jobs): void
    {
        $firstJob = $jobs[0];
        $colony = $firstJob->getColony();
        $field = $this->planetFieldRepository->getByColonyAndFieldIndex(
            $firstJob->getColonyId(),
            $firstJob->getFieldId()
        );

        if ($field === null || !$field->isActive() || $colony->isBlocked()) {
            $this->pauseRunningJobs($jobs);
            return;
        }

        $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction(
            $colony,
            BuildingFunctionEnum::REPAIR_SHIPYARD
        );

        $this->processQueue(
            $jobs,
            $colony,
            $isRepairStationBonus ? 2 : 1,
            $isRepairStationBonus
        );
    }

    private function processStationRepairs(): void
    {
        /** @var array<int, array<StationShipRepair>> $queues */
        $queues = [];
        foreach ($this->stationShipRepairRepository->getAllOrdered() as $repair) {
            $queues[$repair->getStationId()][] = $repair;
        }

        foreach ($queues as $jobs) {
            $this->processStationQueue($jobs);
        }
    }

    /**
     * @param array<StationShipRepair> $jobs
     */
    private function processStationQueue(array $jobs): void
    {
        $station = $jobs[0]->getStation();
        if (!$this->stationUtility->canRepairShips($station)) {
            $this->pauseRunningJobs($jobs);
            return;
        }

        $this->processQueue($jobs, $station, 1, false);
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function processQueue(array $jobs, Colony|Station $entity, int $activeSlotCount, bool $isRepairStationBonus): void
    {
        $time = time();
        $queue = array_values($jobs);

        if ($this->isStoppedScope($queue)) {
            return;
        }

        foreach ($queue as $index => $job) {
            if ($index >= $activeSlotCount) {
                $this->pauseJob($job, $time);
                continue;
            }

            if ($job->getStopDate() > 0) {
                $job->setFinishTime($job->getFinishTime() + ($time - $job->getStopDate()));
                $job->setStopDate(0);
            }

            if ($job->getFinishTime() === 0) {
                $job->setFinishTime($time + $this->repairUtil->getPassiveRepairStepDuration($job->getShip()));
                continue;
            }

            if ($job->getFinishTime() > $time) {
                continue;
            }

            $wrapper = $this->spacecraftWrapperFactory->wrapShip($job->getShip());
            $neededParts = $this->repairUtil->determinePassiveRepairSpareParts(
                $wrapper,
                $isRepairStationBonus,
                true
            );

            if (!$this->repairUtil->enoughSparePartsOnEntity($neededParts, $entity, $job->getShip())) {
                $this->stopScope($queue, $time);
                return;
            }

            $this->repairStep($wrapper, $entity, $neededParts, $isRepairStationBonus);

            if (!$wrapper->canBeRepaired()) {
                $ship = $wrapper->get();
                $ship->getCondition()->setHull($ship->getMaxHull());
                $ship->getCondition()->setState(SpacecraftStateEnum::NONE);

                if ($job instanceof ColonyShipRepair) {
                    $this->colonyShipRepairRepository->delete($job);
                } else {
                    $this->stationShipRepairRepository->delete($job);
                }

                unset($queue[$index]);
                $queue = array_values($queue);

                $nextRepairShip = $this->startPromotedQueueJob(
                    $queue,
                    $activeSlotCount,
                    $time
                );
                $this->sendPrivateMessages($ship, $entity, $nextRepairShip);

                $this->processQueue($queue, $entity, $activeSlotCount, $isRepairStationBonus);
                return;
            }

            $job->setFinishTime($time + $this->repairUtil->getPassiveRepairStepDuration($job->getShip()));
        }
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function pauseRunningJobs(array $jobs): void
    {
        $time = time();

        foreach ($jobs as $job) {
            $this->pauseJob($job, $time);
        }
    }

    private function pauseJob(ColonyShipRepair|StationShipRepair $job, int $time): void
    {
        if ($job->getFinishTime() > 0 && $job->getStopDate() === 0 && !$job->isStopped()) {
            $job->setStopDate($time);
        }
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function stopScope(array $jobs, int $time): void
    {
        foreach ($jobs as $job) {
            $job->setIsStopped(true);
            $job->setStopDate($time);
        }
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
     * @param array<int, int> $neededParts
     */
    private function repairStep(
        ShipWrapperInterface $wrapper,
        Colony|Station $entity,
        array $neededParts,
        bool $isRepairStationBonus
    ): void {
        $ship = $wrapper->get();

        $this->repairHull($ship, $isRepairStationBonus);
        $this->repairShipSystems($wrapper, $isRepairStationBonus);
        $this->repairUtil->consumeSpareParts($neededParts, $entity);
    }

    private function repairHull(Ship $ship, bool $isRepairStationBonus): void
    {
        $condition = $ship->getCondition();
        $hullRepairRate = $isRepairStationBonus
            ? RepairUtil::REPAIR_RATE_PER_TICK * 2
            : RepairUtil::REPAIR_RATE_PER_TICK;

        $condition->changeHull($hullRepairRate);
        if ($condition->getHull() > $ship->getMaxHull()) {
            $condition->setHull($ship->getMaxHull());
        }
    }

    private function repairShipSystems(ShipWrapperInterface $wrapper, bool $isRepairStationBonus): void
    {
        $ship = $wrapper->get();
        $damagedSystems = $wrapper->getDamagedSystems();
        $maxSystems = $isRepairStationBonus ? 4 : 2;

        for ($i = 0; $i < min(count($damagedSystems), $maxSystems); $i++) {
            $system = $damagedSystems[$i];
            $system->setStatus(100);

            if ($ship->getCrewCount() > 0) {
                $system->setMode(
                    $this->spacecraftSystemManager
                        ->lookupSystem($system->getSystemType())
                        ->getDefaultMode()
                );
            }
        }
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    private function startPromotedQueueJob(array $jobs, int $activeSlotCount, int $time): ?Ship
    {
        if (count($jobs) < $activeSlotCount) {
            return null;
        }

        $nextJob = $jobs[$activeSlotCount - 1];
        if ($nextJob->isStopped()) {
            return null;
        }

        if ($nextJob->getStopDate() > 0 && $nextJob->getFinishTime() > 0) {
            $nextJob->setFinishTime($nextJob->getFinishTime() + ($time - $nextJob->getStopDate()));
            $nextJob->setStopDate(0);
            return $nextJob->getShip();
        }

        if ($nextJob->getFinishTime() === 0 && $nextJob->getStopDate() === 0) {
            $nextJob->setFinishTime($time + $this->repairUtil->getPassiveRepairStepDuration($nextJob->getShip()));
            return $nextJob->getShip();
        }

        return null;
    }

    private function sendPrivateMessages(Ship $ship, Colony|Station $entity, ?Ship $nextRepairShip = null): void
    {
        $nextRepairInfo = '';
        if ($nextRepairShip !== null) {
            $nextRepairInfo = sprintf(
                " Nächstes Schiff in Reparatur: %s (nächster Reparaturschritt voraussichtlich um %s).",
                $nextRepairShip->getName(),
                date('d.m.Y H:i', time() + $this->repairUtil->getPassiveRepairStepDuration($nextRepairShip))
            );
        }

        $shipOwnerMessage = $entity instanceof Colony ? sprintf(
            "Die Reparatur der %s wurde in Sektor %s bei der Kolonie %s des Spielers %s fertiggestellt.%s",
            $ship->getName(),
            $ship->getSectorString(),
            $entity->getName(),
            $entity->getUser()->getName(),
            $nextRepairInfo
        ) : sprintf(
            "Die Reparatur der %s wurde in Sektor %s von der %s %s des Spielers %s fertiggestellt.%s",
            $ship->getName(),
            $ship->getSectorString(),
            $entity->getRump()->getName(),
            $entity->getName(),
            $entity->getUser()->getName(),
            $nextRepairInfo
        );

        $this->privateMessageSender->send(
            $entity->getUser()->getId(),
            $ship->getUser()->getId(),
            $shipOwnerMessage,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        $entityOwnerMessage = $entity instanceof Colony ? sprintf(
            "Die Reparatur der %s von Siedler %s wurde in Sektor %s bei der Kolonie %s fertiggestellt.%s",
            $ship->getName(),
            $ship->getUser()->getName(),
            $ship->getSectorString(),
            $entity->getName(),
            $nextRepairInfo
        ) : sprintf(
            "Die Reparatur der %s von Siedler %s wurde in Sektor %s von der %s %s fertiggestellt.%s",
            $ship->getName(),
            $ship->getUser()->getName(),
            $ship->getSectorString(),
            $entity->getRump()->getName(),
            $entity->getName(),
            $nextRepairInfo
        );

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $entity->getUser()->getId(),
            $entityOwnerMessage,
            $entity instanceof Colony ? PrivateMessageFolderTypeEnum::SPECIAL_COLONY : PrivateMessageFolderTypeEnum::SPECIAL_STATION
        );
    }
}
