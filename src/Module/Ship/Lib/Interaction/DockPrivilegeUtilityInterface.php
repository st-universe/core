<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Orm\Entity\UserInterface;

interface DockPrivilegeUtilityInterface
{
    public function checkPrivilegeFor(int $shipId, UserInterface $user): bool;
}
