<?php

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\ShipInterface;

interface LeaveFleetInterface
{
    public function leaveFleet(ShipInterface $ship): bool;
}
