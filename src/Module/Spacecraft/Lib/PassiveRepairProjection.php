<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

final class PassiveRepairProjection
{
    public function __construct(
        private readonly int $finishTime,
        private readonly int $potentialNextWaveTime,
        private readonly int $potentialFinishTime,
        private readonly int $stopDate,
        private readonly bool $isStopped,
        private readonly bool $isActiveRepair
    ) {}

    public function getFinishTime(): int
    {
        return $this->finishTime;
    }

    public function getPotentialNextWaveTime(): int
    {
        return $this->potentialNextWaveTime;
    }

    public function getPotentialFinishTime(): int
    {
        return $this->potentialFinishTime;
    }

    public function getStopDate(): int
    {
        return $this->stopDate;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    public function isActiveRepair(): bool
    {
        return $this->isActiveRepair;
    }
}
