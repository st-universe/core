<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Ship;

interface TrapDetectionInterface
{

    public function isAlertTrap(Location $location, Ship $leadShip): bool;
}
