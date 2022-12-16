<?php

namespace Stu\Component\Ship\Repair;

use Stu\Orm\Entity\ShipInterface;

interface CancelRepairInterface
{
    public function cancelRepair(ShipInterface $ship): bool;
}
