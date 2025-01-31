<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftFactoryInterface
{
    public function create(SpacecraftRumpInterface $rump): SpacecraftInterface;
}
