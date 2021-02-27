<?php

namespace Stu\Module\Ship\View\ShowShip;

use Stu\Component\Ship\AstronomicalMappingEnum;

class AstroStateWrapper
{

    private $state;

    function __construct(int $state)
    {
        $this->state = $state;
    }

    function isPlannable()
    {
        return $this->state == AstronomicalMappingEnum::PLANNABLE;
    }
    function isPlanned()
    {
        return $this->state == AstronomicalMappingEnum::PLANNED;
    }
    function isMeasured()
    {
        return $this->state == AstronomicalMappingEnum::MEASURED;
    }
    function isFinishing()
    {
        return $this->state == AstronomicalMappingEnum::FINISHING;
    }
    function isDone()
    {
        return $this->state == AstronomicalMappingEnum::DONE;
    }
}
