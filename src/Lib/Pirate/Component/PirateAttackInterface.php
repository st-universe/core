<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\Ship;

interface PirateAttackInterface
{
    public function attackShip(FleetWrapperInterface $fleetWrapper, Ship $target): void;
}
