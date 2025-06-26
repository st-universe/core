<?php

namespace Stu\Module\Database\View\Category\Wrapper;

use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;

interface DatabaseCategoryWrapperFactoryInterface
{
    public function createDatabaseCategoryWrapper(
        DatabaseCategory $databaseCategory,
        User $user,
        ?int $layer = null
    ): DatabaseCategoryWrapperInterface;

    public function createDatabaseCategoryEntryWrapper(
        DatabaseEntry $databaseEntry,
        User $user
    ): DatabaseCategoryEntryWrapperInterface;
}
