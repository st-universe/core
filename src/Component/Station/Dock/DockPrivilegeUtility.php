<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

use Override;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;

final class DockPrivilegeUtility implements DockPrivilegeUtilityInterface
{
    public function __construct(private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository) {}

    #[Override]
    public function checkPrivilegeFor(int $stationId, UserInterface|ShipInterface $source): bool
    {
        $privileges = $this->dockingPrivilegeRepository->getByStation($stationId);
        if ($privileges === []) {
            return false;
        }

        $allowed = false;
        $user = $source instanceof UserInterface ? $source : $source->getUser();
        foreach ($privileges as $priv) {
            switch ($priv->getPrivilegeType()) {
                case DockTypeEnum::USER:
                    if ($priv->getTargetId() === $user->getId()) {
                        if ($priv->getPrivilegeMode() == DockModeEnum::DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case DockTypeEnum::ALLIANCE:
                    if ($user->getAlliance() !== null && $priv->getTargetId() === $user->getAlliance()->getId()) {
                        if ($priv->getPrivilegeMode() == DockModeEnum::DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case DockTypeEnum::FACTION:
                    if ($priv->getTargetId() == $user->getFactionId()) {
                        if ($priv->getPrivilegeMode() == DockModeEnum::DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case DockTypeEnum::SHIP:
                    if (
                        $source instanceof ShipInterface
                        && $priv->getTargetId() == $source->getId()
                    ) {
                        if ($priv->getPrivilegeMode() == DockModeEnum::DENY) {
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
