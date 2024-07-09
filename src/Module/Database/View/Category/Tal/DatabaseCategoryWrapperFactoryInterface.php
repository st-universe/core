<?php

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;

interface DatabaseCategoryWrapperFactoryInterface
{
    public function createDatabaseCategoryWrapper(
        DatabaseCategoryInterface $databaseCategory,
        UserInterface $user
    ): DatabaseCategoryWrapperInterface;

    public function createDatabaseCategoryEntryWrapper(
        DatabaseEntryInterface $databaseEntry,
        UserInterface $user
    ): DatabaseCategoryEntryWrapperInterface;
}
