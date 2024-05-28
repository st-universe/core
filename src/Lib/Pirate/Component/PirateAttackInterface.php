<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface PirateAttackInterface
{
    public function attackShip(FleetWrapperInterface $fleetWrapper, ShipInterface $target): void;
}
