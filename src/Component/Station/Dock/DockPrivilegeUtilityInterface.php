<?php

namespace Stu\Component\Station\Dock;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;

interface DockPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(StationInterface $station, UserInterface|ShipInterface $source): bool;
}
