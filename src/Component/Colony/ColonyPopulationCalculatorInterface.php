<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

interface ColonyPopulationCalculatorInterface
{
    public function getFreeAssignmentCount(): int;

    public function getCrewLimit(): int;

    public function getLifeStandardPercentage(): int;

    public function getNegativeEffect(): int;

    public function getPositiveEffectPrimary(): int;

    public function getPositiveEffectSecondary(): int;

    public function getGrowth(): int;
}
