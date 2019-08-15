<?php

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;

interface DatabaseCategoryTalFactoryInterface
{
    public function createDatabaseCategoryTal(
        DatabaseCategoryInterface $databaseCategory
    ): DatabaseCategoryTalInterface;

    public function createDatabaseCategoryEntryTal(
        DatabaseEntryInterface $databaseEntry
    );
}