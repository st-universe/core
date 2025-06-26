<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Override;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class DatabaseCategoryWrapperFactory implements DatabaseCategoryWrapperFactoryInterface
{
    public function __construct(
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private StarSystemRepositoryInterface $starSystemRepository,
        private ColonyClassRepositoryInterface $colonyClassRepositoryInterface,
        private StationRepositoryInterface $stationRepository,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository
    ) {}

    #[Override]
    public function createDatabaseCategoryWrapper(
        DatabaseCategory $databaseCategory,
        User $user,
        ?int $layer = null
    ): DatabaseCategoryWrapperInterface {
        return new DatabaseCategoryWrapper(
            $this,
            $databaseCategory,
            $user,
            $this->databaseEntryRepository,
            $layer
        );
    }

    #[Override]
    public function createDatabaseCategoryEntryWrapper(
        DatabaseEntry $databaseEntry,
        User $user
    ): DatabaseCategoryEntryWrapperInterface {
        return new DatabaseCategoryEntryWrapper(
            $this->databaseUserRepository,
            $databaseEntry,
            $this->starSystemRepository,
            $this->stationRepository,
            $this->colonyClassRepositoryInterface,
            $user
        );
    }
}
