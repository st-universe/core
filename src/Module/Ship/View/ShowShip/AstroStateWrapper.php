<?php

namespace Stu\Module\Ship\View\ShowShip;

use Stu\Component\Ship\AstronomicalMappingStateEnum;

class AstroStateWrapper
{
    public function __construct(private AstronomicalMappingStateEnum $state, private ?int $turnsLeft, private bool $isSystem, private ?int $measurementpointsleft) {}

    public function isPlannable(): bool
    {
        return $this->state === AstronomicalMappingStateEnum::PLANNABLE;
    }
    public function isPlanned(): bool
    {
        return $this->state === AstronomicalMappingStateEnum::PLANNED;
    }
    public function isMeasured(): bool
    {
        return $this->state === AstronomicalMappingStateEnum::MEASURED;
    }
    public function isFinishing(): bool
    {
        return $this->state === AstronomicalMappingStateEnum::FINISHING;
    }
    public function isDone(): bool
    {
        return $this->state === AstronomicalMappingStateEnum::DONE;
    }
    public function getTurnsLeft(): ?int
    {
        return $this->turnsLeft;
    }
    public function getMeasurepointsLeft(): ?int
    {
        return $this->measurementpointsleft;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function getType(): string
    {
        return $this->isSystem() ? 'System' : 'Region';
    }
}
