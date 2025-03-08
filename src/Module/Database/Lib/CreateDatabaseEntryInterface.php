<?php

namespace Stu\Module\Database\Lib;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

interface CreateDatabaseEntryInterface
{
    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface;

    public function checkForCategoryCompletion(
        UserInterface $user,
        DatabaseCategoryInterface $category,
        ?int $finishedDatabaseEntryId = null
    ): void;
}
