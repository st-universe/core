<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Creates ui and station related items
 */
final class StationUiFactory implements StationUiFactoryInterface
{
    private UserRepositoryInterface $userRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private FactionRepositoryInterface $factionRepository;

    private ShipRepositoryInterface $shipRepository;

    private PanelLayerCreationInterface $panelLayerCreation;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AllianceRepositoryInterface $allianceRepository,
        FactionRepositoryInterface $factionRepository,
        ShipRepositoryInterface $shipRepository,
        PanelLayerCreationInterface $panelLayerCreation
    ) {
        $this->userRepository = $userRepository;
        $this->allianceRepository = $allianceRepository;
        $this->factionRepository = $factionRepository;
        $this->shipRepository = $shipRepository;
        $this->panelLayerCreation = $panelLayerCreation;
    }

    public function createSystemScanPanel(
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        StarSystemInterface $system
    ): SystemScanPanel {
        return new SystemScanPanel(
            $this->panelLayerCreation,
            $currentShip,
            $system,
            $user,
            $loggerUtil
        );
    }

    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
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
