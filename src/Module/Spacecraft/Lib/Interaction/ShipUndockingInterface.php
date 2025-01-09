<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;

interface ShipUndockingInterface
{
    public function undockShip(StationInterface $station, ShipInterface $dockedShip): void;

    public function undockAllDocked(StationInterface $station): bool;
}
