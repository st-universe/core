<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

interface StationUiFactoryInterface
{
    public function createSystemScanPanel(
        SpacecraftWrapperInterface $currentWrapper,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        StarSystemInterface $system
    ): SystemScanPanel;

    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
    ): DockingPrivilegeItem;
}
