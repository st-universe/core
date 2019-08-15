<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;

final class DatabaseCategoryTalFactory implements DatabaseCategoryTalFactoryInterface
{

    public function createDatabaseCategoryTal(
        DatabaseCategoryInterface $databaseCategory
    ): DatabaseCategoryTalInterface {
        return new DatabaseCategoryTal(
            $this,
            $databaseCategory
        );
    }

    public function createDatabaseCategoryEntryTal(
        DatabaseEntryInterface $databaseEntry
    ) {
        return new DatabaseCategoryEntryTal(
            $databaseEntry
        );
    }
}