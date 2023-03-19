<?php

namespace Stu\Module\Ship\View\ShowShip;

use Stu\Component\Ship\AstronomicalMappingEnum;

class AstroStateWrapper
{
    private $state;

    public function __construct(int $state)
    {
        $this->state = $state;
    }

    public function isPlannable()
    {
        return $this->state == AstronomicalMappingEnum::PLANNABLE;
    }
    public function isPlanned()
    {
        return $this->state == AstronomicalMappingEnum::PLANNED;
    }
    public function isMeasured()
    {
        return $this->state == AstronomicalMappingEnum::MEASURED;
    }
    public function isFinishing()
    {
        return $this->state == AstronomicalMappingEnum::FINISHING;
    }
    public function isDone()
    {
        return $this->state == AstronomicalMappingEnum::DONE;
    }
}
