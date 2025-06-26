<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;

interface CreatePrestigeLogInterface
{
    public function createLog(
        int $amount,
        string $description,
        User $user,
        int $date
    ): void;

    public function createLogForDatabaseEntry(
        DatabaseEntry $databaseEntry,
        User $user,
        int $date
    ): void;
}
