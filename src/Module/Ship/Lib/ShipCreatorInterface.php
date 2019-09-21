<?php

namespace Stu\Module\Ship\Lib;

use ShipData;
use Stu\Orm\Entity\ColonyInterface;

interface ShipCreatorInterface
{
    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyInterface $colony = null): ShipData;
}