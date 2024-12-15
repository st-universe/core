<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Orm\Entity\StationInterface;

interface ShipUndockingInterface
{
    public function undockAllDocked(StationInterface $station): bool;
}
