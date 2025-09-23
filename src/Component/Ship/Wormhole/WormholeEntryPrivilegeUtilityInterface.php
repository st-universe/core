<?php

namespace Stu\Component\Ship\Wormhole;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\User;

interface WormholeEntryPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(WormholeEntry $wormholeEntry, User|Spacecraft $source): bool;
}