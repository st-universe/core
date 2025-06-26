<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

use Override;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;

final class DockPrivilegeUtility implements DockPrivilegeUtilityInterface
{
    #[Override]
    public function checkPrivilegeFor(Station $station, User|Ship $source): bool
    {
        try {
            return $station->getDockPrivileges()->reduce(
                fn(bool $isAllowed, DockingPrivilege $privilege): bool => $isAllowed || $this->isAllowed($privilege, $source),
                false
            );
        } catch (DockingUnallowedException) {
            return false;
        }
    }

    private function isAllowed(DockingPrivilege $privilege, User|Ship $source): bool
    {
        $user = $source instanceof User ? $source : $source->getUser();

        $isMatch = match ($privilege->getPrivilegeType()) {
            DockTypeEnum::USER => $privilege->getTargetId() === $user->getId(),
            DockTypeEnum::ALLIANCE => $user->getAlliance() !== null && $privilege->getTargetId() === $user->getAlliance()->getId(),
            DockTypeEnum::FACTION => $privilege->getTargetId() == $user->getFactionId(),
            DockTypeEnum::SHIP => $source instanceof Ship && $privilege->getTargetId() == $source->getId(),
        };

        if (!$isMatch) {
            return false;
        }

        if ($privilege->getPrivilegeMode() === DockModeEnum::DENY) {
            throw new DockingUnallowedException();
        }

        return true;
    }
}
