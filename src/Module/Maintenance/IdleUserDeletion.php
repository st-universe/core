<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Player\Deletion\PlayerDeletion;

final class IdleUserDeletion implements MaintenanceHandlerInterface
{

    public function handle(): void
    {
        PlayerDeletion::handleIdleUsers();
    }
}
