<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;

final class PassiveRepairProgressWrapper
{
    public function __construct(
        private readonly ShipWrapperInterface $wrapper,
        private readonly int $finishTime,
        private readonly int $potentialNextWaveTime,
        private readonly int $potentialFinishTime,
        private readonly int $stopDate,
        private readonly bool $isStopped,
        private readonly bool $isActiveRepair,
        private readonly ?int $fieldId = null
    ) {}

    public function get(): Spacecraft
    {
        return $this->wrapper->get();
    }

    /**
     * @return array<SpacecraftSystem>
     */
    public function getDamagedSystems(): array
    {
        return $this->wrapper->getDamagedSystems();
    }

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

    public function getFieldId(): ?int
    {
        return $this->fieldId;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    public function isPaused(): bool
    {
        return !$this->isStopped && $this->stopDate > 0;
    }

    public function isActiveRepair(): bool
    {
        return $this->isActiveRepair;
    }

    public function isQueued(): bool
    {
        return !$this->isStopped && $this->stopDate === 0 && $this->finishTime === 0;
    }
}
