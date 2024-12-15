<?php

namespace Stu\Component\Spacecraft\Repair;

use Stu\Orm\Entity\SpacecraftInterface;

interface CancelRepairInterface
{
    public function cancelRepair(SpacecraftInterface $spacecraft): bool;
}
