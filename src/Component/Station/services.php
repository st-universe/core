<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Component\Station\Dock\DockPrivilegeUtility;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;

use function DI\autowire;

return [
    StationUtilityInterface::class => autowire(StationUtility::class),
    DockPrivilegeUtilityInterface::class => autowire(DockPrivilegeUtility::class)
];
