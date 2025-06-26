<?php

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\Ship;

interface LeaveFleetInterface
{
    public function leaveFleet(Ship $ship): bool;
}
