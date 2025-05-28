<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Wrapper;

use Override;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
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
        DatabaseCategoryInterface $databaseCategory,
        UserInterface $user,
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
        DatabaseEntryInterface $databaseEntry,
        UserInterface $user
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
