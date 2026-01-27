<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;

interface StationUiFactoryInterface
{
    public function createSystemScanPanel(
        SpacecraftWrapperInterface $currentWrapper,
        User $user,
        LoggerUtilInterface $loggerUtil,
        StarSystem $system,
        bool $tachyonFresh
    ): SystemScanPanel;

    public function createDockingPrivilegeItem(
        DockingPrivilege $dockingPrivilege
    ): DockingPrivilegeItem;
}
