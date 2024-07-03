<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Override;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;

final class DockPrivilegeUtility implements DockPrivilegeUtilityInterface
{
    public function __construct(private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository)
    {
    }

    #[Override]
    public function checkPrivilegeFor(int $shipId, UserInterface $user): bool
    {
        $privileges = $this->dockingPrivilegeRepository->getByShip($shipId);
        if ($privileges === []) {
            return false;
        }
        $allowed = false;
        foreach ($privileges as $priv) {
            switch ($priv->getPrivilegeType()) {
                case ShipEnum::DOCK_PRIVILEGE_USER:
                    if ($priv->getTargetId() === $user->getId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case ShipEnum::DOCK_PRIVILEGE_ALLIANCE:
                    if ($user->getAlliance() !== null && $priv->getTargetId() === $user->getAlliance()->getId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case ShipEnum::DOCK_PRIVILEGE_FACTION:
                    if ($priv->getTargetId() == $user->getFactionId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
            }
        }
        return $allowed;
    }
}
