<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface PlanetColonizationInterface
{
    public function colonize(
        ColonyInterface $colony,
        int $userId,
        BuildingInterface $building,
        ?PlanetFieldInterface $field = null
    ): void;
}