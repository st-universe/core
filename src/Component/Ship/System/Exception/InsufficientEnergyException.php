<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Exception;

final class InsufficientEnergyException extends ShipSystemException
{
    public function __construct(private int $neededEnergy)
    {
    }

    public function getNeededEnergy(): int
    {
        return $this->neededEnergy;
    }
}
