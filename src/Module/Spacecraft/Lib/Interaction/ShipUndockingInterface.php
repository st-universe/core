<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;

interface ShipUndockingInterface
{
    public function undockShip(Station $station, Ship $dockedShip): void;

    public function undockAllDocked(Station $station): bool;
}
