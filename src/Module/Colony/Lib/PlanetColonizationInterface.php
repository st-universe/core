<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;

interface PlanetColonizationInterface
{
    public function colonize(
        Colony $colony,
        User $user,
        Building $building,
        ?PlanetField $field = null
    ): void;
}
