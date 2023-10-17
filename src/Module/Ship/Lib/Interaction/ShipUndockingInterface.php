<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Orm\Entity\ShipInterface;

interface ShipUndockingInterface
{
    public function undockAllDocked(ShipInterface $station): bool;
}
