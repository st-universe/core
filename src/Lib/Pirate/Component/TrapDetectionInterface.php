<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;

interface TrapDetectionInterface
{

    public function isAlertTrap(LocationInterface $location, ShipInterface $leadShip): bool;
}
