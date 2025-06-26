<?php

namespace Stu\Component\Spacecraft\Repair;

use Stu\Orm\Entity\Spacecraft;

interface CancelRepairInterface
{
    public function cancelRepair(Spacecraft $spacecraft): bool;
}
