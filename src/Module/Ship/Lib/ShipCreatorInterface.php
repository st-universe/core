<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipCreatorInterface
{
    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyInterface $colony = null): ShipInterface;
}