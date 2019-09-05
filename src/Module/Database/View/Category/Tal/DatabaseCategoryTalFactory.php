<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use UserData;

final class DatabaseCategoryTalFactory implements DatabaseCategoryTalFactoryInterface
{
    private $databaseUserRepository;

    private $starSystemRepository;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository,
        StarSystemRepositoryInterface $starSystemRepository
    ) {
        $this->databaseUserRepository = $databaseUserRepository;
        $this->starSystemRepository = $starSystemRepository;
    }

    public function createDatabaseCategoryTal(
        DatabaseCategoryInterface $databaseCategory,
        UserData $user
    ): DatabaseCategoryTalInterface {
        return new DatabaseCategoryTal(
            $this,
            $databaseCategory,
            $user
        );
    }

    public function createDatabaseCategoryEntryTal(
        DatabaseEntryInterface $databaseEntry,
        UserData $user
    ): DatabaseCategoryEntryTalInterface {
        return new DatabaseCategoryEntryTal(
            $this->databaseUserRepository,
            $databaseEntry,
            $this->starSystemRepository,
            $user
        );
    }
}