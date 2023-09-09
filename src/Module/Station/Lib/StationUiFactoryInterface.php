<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Lib\Map\VisualPanel\SystemScanPanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

interface StationUiFactoryInterface
{
    public function createSystemScanPanel(
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        StarSystemInterface $system
    ): SystemScanPanel;

    public function createSystemScanPanelEntry(
        VisualPanelEntryData $data,
        StarSystemInterface $system
    ): SystemScanPanelEntry;

    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
    ): DockingPrivilegeItem;
}
