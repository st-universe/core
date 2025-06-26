<?php

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;

interface CreateDatabaseEntryInterface
{
    public function createDatabaseEntryForUser(User $user, int $databaseEntryId): ?DatabaseEntry;

    public function checkForCategoryCompletion(
        User $user,
        DatabaseCategory $category,
        ?int $finishedDatabaseEntryId = null
    ): void;
}
