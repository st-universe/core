<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;

return [
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    MapCycle::class => autowire(MapCycle::class),
    DatabaseOptimization::class => autowire(DatabaseOptimization::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class),
    ExpiredInvitationTokenDeletion::class => autowire(ExpiredInvitationTokenDeletion::class),
];
