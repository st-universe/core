<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;

interface SpacecraftFactoryInterface
{
    public function create(SpacecraftRump $rump): Spacecraft;
}
