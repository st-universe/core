<?php

namespace Stu\Module\Maintenance;

use Stu\Lib\UserDeletion;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{

    public function handle(): void
    {
        UserDeletion::handleIdleUsers();
    }
}
