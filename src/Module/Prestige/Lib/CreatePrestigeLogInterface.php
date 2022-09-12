<?php

namespace Stu\Module\Prestige\Lib;

use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

interface CreatePrestigeLogInterface
{
    public function createLog(
        int $amount,
        string $description,
        UserInterface $user,
        int $date
    ): void;

    public function createLogForDatabaseEntry(
        DatabaseEntryInterface $databaseEntry,
        UserInterface $user,
        int $date
    ): void;
}
