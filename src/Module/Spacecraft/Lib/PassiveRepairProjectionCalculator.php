<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StationShipRepair;

final class PassiveRepairProjectionCalculator implements PassiveRepairProjectionCalculatorInterface
{
    public function __construct(
        private readonly RepairUtilInterface $repairUtil,
        private readonly StuTime $stuTime
    ) {}

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     * @return array<int, PassiveRepairProjection>
     */
    #[\Override]
    public function calculate(array $jobs, int $activeSlotCount, bool $isRepairStationBonus): array
    {
        $time = $this->stuTime->time();
        $scopeStopped = $this->isStoppedScope($jobs);
        $slotAvailableAt = array_fill(0, max(1, $activeSlotCount), $time);
        $result = [];

        foreach ($jobs as $job) {
            $finishTime = $job->getFinishTime();
            $stopDate = $job->getStopDate();
            $isStopped = $job->isStopped();
            $potentialNextWaveTime = 0;
            $potentialFinishTime = 0;
            $isActiveRepair = false;

            if (!$scopeStopped) {
                $ship = $job->getShip();
                $stepDuration = $this->repairUtil->getPassiveRepairStepDuration($ship);
                $remainingDuration = $this->determineRemainingDuration(
                    $ship,
                    $isRepairStationBonus,
                    $stepDuration,
                    $finishTime,
                    $stopDate,
                    $time
                );

                $slot = $this->getNextAvailableSlot($slotAvailableAt);
                $startsAt = max($time, $slotAvailableAt[$slot]);

                $potentialFinishTime = $startsAt + $remainingDuration;

                if ($finishTime > 0) {
                    $remainingCurrentStep = $stopDate > 0
                        ? max(0, $finishTime - $stopDate)
                        : max(0, $finishTime - $time);
                    $potentialNextWaveTime = $startsAt + $remainingCurrentStep;
                } else {
                    $potentialNextWaveTime = $startsAt + min($remainingDuration, $stepDuration);
                }

                $isActiveRepair = $startsAt === $time
                    && !$isStopped
                    && $stopDate === 0
                    && $finishTime > 0;

                $slotAvailableAt[$slot] = $potentialFinishTime;
            }

            $result[$job->getShipId()] = new PassiveRepairProjection(
                $finishTime,
                $potentialNextWaveTime,
                $potentialFinishTime,
                $stopDate,
                $isStopped,
                $isActiveRepair
            );
        }

        return $result;
    }

    /**
     * @param array<ColonyShipRepair|StationShipRepair> $jobs
     */
    #[\Override]
    public function getPotentialFinishTime(
        array $jobs,
        int $activeSlotCount,
        bool $isRepairStationBonus,
        int $shipId
    ): int {
        $projection = $this->calculate(
            $jobs,
            $activeSlotCount,
            $isRepairStationBonus
        )[$shipId] ?? null;

        return $projection?->getPotentialFinishTime() ?? 0;
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
        Spacecraft $spacecraft,
        bool $isRepairStationBonus,
        int $stepDuration,
        int $finishTime,
        int $stopDate,
        int $time
    ): int {
        $totalDuration = $this->repairUtil->getPassiveRepairEstimatedDurationForSpacecraft(
            $spacecraft,
            $isRepairStationBonus
        );

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
