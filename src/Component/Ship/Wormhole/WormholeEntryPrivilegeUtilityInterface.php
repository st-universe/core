<?php

namespace Stu\Component\Ship\Wormhole;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\WormholeEntry;

interface WormholeEntryPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(WormholeEntry $wormholeEntry, User|Spacecraft $source): bool;
}
