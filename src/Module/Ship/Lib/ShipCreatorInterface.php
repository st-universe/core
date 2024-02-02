<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;

interface ShipCreatorInterface
{
    public function createBy(
        int $userId,
        int $shipRumpId,
        int $shipBuildplanId,
        ?ColonyInterface $colony = null,
        ?ConstructionProgressInterface $progress = null
    ): ShipConfiguratorInterface;
}
