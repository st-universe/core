<?php

namespace Stu\Module\Ship\View\ShowShip;

use Stu\Component\Ship\AstronomicalMappingEnum;

class AstroStateWrapper
{
    private int $state;

    private ?int $turnsLeft;

    private bool $isSystem;

    public function __construct(int $state, ?int $turnsLeft, bool $isSystem)
    {
        $this->state = $state;
        $this->turnsLeft = $turnsLeft;
        $this->isSystem = $isSystem;
    }

    public function isPlannable(): bool
    {
        return $this->state == AstronomicalMappingEnum::PLANNABLE;
    }
    public function isPlanned(): bool
    {
        return $this->state == AstronomicalMappingEnum::PLANNED;
    }
    public function isMeasured(): bool
    {
        return $this->state == AstronomicalMappingEnum::MEASURED;
    }
    public function isFinishing(): bool
    {
        return $this->state == AstronomicalMappingEnum::FINISHING;
    }
    public function isDone(): bool
    {
        return $this->state == AstronomicalMappingEnum::DONE;
    }
    public function getTurnsLeft(): ?int
    {
        return $this->turnsLeft;
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
