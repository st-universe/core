<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

interface PlanetColonizationInterface
{
    public function colonize(
        Colony $colony,
        int $userId,
        Building $building,
        ?PlanetField $field = null
    ): void;
}
