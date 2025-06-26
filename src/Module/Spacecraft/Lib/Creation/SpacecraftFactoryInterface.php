<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;

interface SpacecraftFactoryInterface
{
    public function create(SpacecraftRump $rump): Spacecraft;
}
