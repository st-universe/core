<?php

namespace Stu\Module\Colony\Lib;

use Colony;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface PlanetColonizationInterface
{
    public function colonize(
        Colony $colony,
        int $userId,
        BuildingInterface $building,
        ?PlanetFieldInterface $field = null
    ): void;
}