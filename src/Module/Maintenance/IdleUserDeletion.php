<?php

namespace Stu\Module\Maintenance;

use UserDeletion;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{

    public function handle(): void
    {
        UserDeletion::handleIdleUsers();
    }
}
