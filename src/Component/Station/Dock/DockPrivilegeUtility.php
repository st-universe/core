<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

use Override;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;

final class DockPrivilegeUtility implements DockPrivilegeUtilityInterface
{
    public function __construct(private readonly DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository) {}

    #[Override]
    public function checkPrivilegeFor(StationInterface $station, UserInterface|ShipInterface $source): bool
    {
        try {
            return array_reduce(
                $this->dockingPrivilegeRepository->getByStation($station),
                fn(bool $isAllowed, DockingPrivilegeInterface $privilege): bool => $isAllowed || $this->isAllowed($privilege, $source),
                false
            );
        } catch (DockingUnallowedException) {
            return false;
        }
    }

    private function isAllowed(DockingPrivilegeInterface $privilege, UserInterface|ShipInterface $source): bool
    {
        $user = $source instanceof UserInterface ? $source : $source->getUser();

        $isMatch = match ($privilege->getPrivilegeType()) {
            DockTypeEnum::USER => $privilege->getTargetId() === $user->getId(),
            DockTypeEnum::ALLIANCE => $user->getAlliance() !== null && $privilege->getTargetId() === $user->getAlliance()->getId(),
            DockTypeEnum::FACTION => $privilege->getTargetId() == $user->getFactionId(),
            DockTypeEnum::SHIP => $source instanceof ShipInterface && $privilege->getTargetId() == $source->getId(),
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
