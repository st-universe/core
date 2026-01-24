<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

interface TrapDetectionInterface
{

    public function isAlertTrap(Location $location, Spacecraft $leadSpacecraft): bool;
}
