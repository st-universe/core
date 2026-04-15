<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\StationShipRepair;

final class PassiveRepairProgressBuilder
{
    public function __construct(
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly RepairUtilInterface $repairUtil
    ) {}

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     * @return array<PassiveRepairProgressWrapper>
     */
    public function build(array $jobs, int $activeSlotCount, bool $isRepairStationBonus): array
    {
        $time = time();
        $scopeStopped = $this->isStoppedScope($jobs);
        $slotAvailableAt = array_fill(0, max(1, $activeSlotCount), $time);
        $result = [];

        foreach ($jobs as $job) {
            $wrapper = $this->spacecraftWrapperFactory->wrapShip($job->getShip());
            $stepDuration = $this->repairUtil->getPassiveRepairStepDuration($job->getShip());
            $remainingDuration = $this->determineRemainingDuration(
                $wrapper,
                $isRepairStationBonus,
                $stepDuration,
                $job->getFinishTime(),
                $job->getStopDate(),
                $time
            );

            $potentialNextWaveTime = 0;
            $potentialFinishTime = 0;
            $isActiveRepair = false;

            if (!$scopeStopped) {
                $slot = $this->getNextAvailableSlot($slotAvailableAt);
                $startsAt = max($time, $slotAvailableAt[$slot]);

                $potentialFinishTime = $startsAt + $remainingDuration;

                if ($job->getFinishTime() > 0) {
                    $remainingCurrentStep = $job->getStopDate() > 0
                        ? max(0, $job->getFinishTime() - $job->getStopDate())
                        : max(0, $job->getFinishTime() - $time);
                    $potentialNextWaveTime = $startsAt + $remainingCurrentStep;
                } else {
                    $potentialNextWaveTime = $startsAt + min($remainingDuration, $stepDuration);
                }

                $isActiveRepair = $startsAt === $time
                    && !$job->isStopped()
                    && $job->getStopDate() === 0
                    && $job->getFinishTime() > 0;

                $slotAvailableAt[$slot] = $potentialFinishTime;
            }

            $result[] = new PassiveRepairProgressWrapper(
                $wrapper,
                $job->getFinishTime(),
                $potentialNextWaveTime,
                $potentialFinishTime,
                $job->getStopDate(),
                $job->isStopped(),
                $isActiveRepair,
                $job instanceof ColonyShipRepair ? $job->getFieldId() : null
            );
        }

        return $result;
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
     * @param array<int, int> $slotAvailableAt
     */
    private function getNextAvailableSlot(array $slotAvailableAt): int
    {
        $slot = 0;
        $earliest = $slotAvailableAt[0];

        foreach ($slotAvailableAt as $index => $availableAt) {
            if ($availableAt < $earliest) {
                $earliest = $availableAt;
                $slot = $index;
            }
        }

        return $slot;
    }

    private function determineRemainingDuration(
        ShipWrapperInterface $wrapper,
        bool $isRepairStationBonus,
        int $stepDuration,
        int $finishTime,
        int $stopDate,
        int $time
    ): int {
        $totalDuration = $this->repairUtil->getPassiveRepairEstimatedDuration($wrapper, $isRepairStationBonus);

        if ($finishTime <= 0) {
            return $totalDuration;
        }

        $remainingCurrentStep = $stopDate > 0
            ? max(0, $finishTime - $stopDate)
            : max(0, $finishTime - $time);
        $elapsedCurrentStep = max(0, $stepDuration - $remainingCurrentStep);

        return max($remainingCurrentStep, $totalDuration - $elapsedCurrentStep);
    }
}
