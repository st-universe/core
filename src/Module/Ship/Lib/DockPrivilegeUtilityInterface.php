<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\UserInterface;

interface DockPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(int $shipId, UserInterface $user): bool;
}
