<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class DatabaseCategoryTalFactory implements DatabaseCategoryTalFactoryInterface
{
    private $databaseUserRepository;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository
    )
    {
        $this->databaseUserRepository = $databaseUserRepository;
    }

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
            $this->databaseUserRepository,
            $databaseEntry
        );
    }
}