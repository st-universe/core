<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Creates ui and station related items
 */
final class StationUiFactory implements StationUiFactoryInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private FactionRepositoryInterface $factionRepository,
        private ShipRepositoryInterface $shipRepository,
        private PanelLayerCreationInterface $panelLayerCreation
    ) {}

    #[\Override]
    public function createSystemScanPanel(
        SpacecraftWrapperInterface $currentWrapper,
        User $user,
        LoggerUtilInterface $loggerUtil,
        StarSystem $system
    ): SystemScanPanel {
        return new SystemScanPanel(
            $this->panelLayerCreation,
            $currentWrapper,
            $system,
            $user,
            $loggerUtil
        );
    }

    #[\Override]
    public function createDockingPrivilegeItem(
        DockingPrivilege $dockingPrivilege
    ): DockingPrivilegeItem {
        return new DockingPrivilegeItem(
            $this->userRepository,
            $this->allianceRepository,
            $this->factionRepository,
            $this->shipRepository,
            $dockingPrivilege
        );
    }
}
