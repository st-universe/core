<?php

namespace Stu\Component\Station\Dock;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface DockPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(int $stationId, UserInterface|ShipInterface $source): bool;
}
