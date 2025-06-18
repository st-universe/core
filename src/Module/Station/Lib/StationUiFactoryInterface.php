<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

interface StationUiFactoryInterface
{
    public function createSystemScanPanel(
        SpacecraftInterface $currentSpacecraft,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        StarSystemInterface $system
    ): SystemScanPanel;

    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
    ): DockingPrivilegeItem;
}
