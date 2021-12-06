<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Exception;

final class InsufficientEnergyException extends ShipSystemException
{
    private int $neededEnergy;

    public function __construct(int $neededEnergy)
    {
        $this->neededEnergy = $neededEnergy;
    }

    public function getNeededEnergy(): int
    {
        return $this->neededEnergy;
    }
}
