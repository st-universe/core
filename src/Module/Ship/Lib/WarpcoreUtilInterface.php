<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface WarpcoreUtilInterface
{
    public function loadWarpcore(
        ShipInterface $ship,
        int $additionalLoad,
        ?ColonyInterface $colony = null,
        ?ShipInterface $station = null
    ): ?string;
}
