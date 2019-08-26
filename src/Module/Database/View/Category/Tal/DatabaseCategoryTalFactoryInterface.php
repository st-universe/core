<?php

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use UserData;

interface DatabaseCategoryTalFactoryInterface
{
    public function createDatabaseCategoryTal(
        DatabaseCategoryInterface $databaseCategory,
        UserData $user
    ): DatabaseCategoryTalInterface;

    public function createDatabaseCategoryEntryTal(
        DatabaseEntryInterface $databaseEntry,
        UserData $user
    ): DatabaseCategoryEntryTalInterface;
}