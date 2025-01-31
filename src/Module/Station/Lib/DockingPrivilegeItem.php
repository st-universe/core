<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DockingPrivilegeItem
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private FactionRepositoryInterface $factionRepository,
        private ShipRepositoryInterface $shipRepository,
        private DockingPrivilegeInterface $dockingPrivilege
    ) {}

    public function getId(): int
    {
        return $this->dockingPrivilege->getId();
    }

    public function getTargetName(): string
    {
        switch ($this->dockingPrivilege->getPrivilegeType()) {
            case DockTypeEnum::USER:
                $user = $this->userRepository->find($this->dockingPrivilege->getTargetId());
                return $user === null
                    ? 'nicht mehr vorhanden'
                    : $user->getName();
            case DockTypeEnum::ALLIANCE:
                $ally = $this->allianceRepository->find($this->dockingPrivilege->getTargetId());
                return $ally === null
                    ? 'nicht mehr vorhanden'
                    : $ally->getName();
            case DockTypeEnum::FACTION:
                $faction = $this->factionRepository->find($this->dockingPrivilege->getTargetId());
                return $faction === null
                    ? 'nicht mehr vorhanden' :
                    $faction->getName();
            case DockTypeEnum::SHIP:
                $ship = $this->shipRepository->find($this->dockingPrivilege->getTargetId());
                return $ship === null
                    ? 'nicht mehr vorhanden'
                    : $ship->getName();
        }
    }

    public function getPrivilegeModeString(): string
    {
        return $this->dockingPrivilege->getPrivilegeMode()->getDescription();
    }

    public function isDockingAllowed(): bool
    {
        return $this->dockingPrivilege->getPrivilegeMode() == DockModeEnum::ALLOW;
    }
}
