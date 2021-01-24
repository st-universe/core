<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;

return [
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    MapCycle::class => autowire(MapCycle::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class),
    ExpiredInvitationTokenDeletion::class => autowire(ExpiredInvitationTokenDeletion::class),
    //TODO clean old tachy scans
    //TODO clean unallowed fleets
];
