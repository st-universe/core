<?php

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

interface CreateDatabaseEntryInterface
{
    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface;
}
