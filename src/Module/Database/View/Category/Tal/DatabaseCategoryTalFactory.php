<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category\Tal;

use Override;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class DatabaseCategoryTalFactory implements DatabaseCategoryTalFactoryInterface
{
    public function __construct(private DatabaseUserRepositoryInterface $databaseUserRepository, private StarSystemRepositoryInterface $starSystemRepository, private ColonyClassRepositoryInterface $colonyClassRepositoryInterface, private ShipRepositoryInterface $shipRepository)
    {
    }

    #[Override]
    public function createDatabaseCategoryTal(
        DatabaseCategoryInterface $databaseCategory,
        UserInterface $user
    ): DatabaseCategoryTalInterface {
        return new DatabaseCategoryTal(
            $this,
            $databaseCategory,
            $user
        );
    }

    #[Override]
    public function createDatabaseCategoryEntryTal(
        DatabaseEntryInterface $databaseEntry,
        UserInterface $user
    ): DatabaseCategoryEntryTalInterface {
        return new DatabaseCategoryEntryTal(
            $this->databaseUserRepository,
            $databaseEntry,
            $this->starSystemRepository,
            $this->shipRepository,
            $this->colonyClassRepositoryInterface,
            $user
        );
    }
}
