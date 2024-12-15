<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Exception;

final class InsufficientEnergyException extends SpacecraftSystemException
{
    public function __construct(private int $neededEnergy) {}

    public function getNeededEnergy(): int
    {
        return $this->neededEnergy;
    }
}
