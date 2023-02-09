<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;


use Stu\Orm\Entity\DockingPrivilegeInterface;

interface StationUiFactoryInterface
{
    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
    ): DockingPrivilegeItem;
}
