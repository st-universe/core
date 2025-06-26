<?php

namespace Stu\Component\Station\Dock;

use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;

interface DockPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(Station $station, User|Ship $source): bool;
}
