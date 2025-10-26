<?php

namespace Stu\Module\Maintenance;

use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateMissingUserAwards implements MaintenanceHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private CreateDatabaseEntryInterface $createDatabaseEntry
    ) {}

    #[\Override]
    public function handle(): void
    {
        $categories = $this->databaseCategoryRepository->findAll();

        foreach ($this->userRepository->getNonNpcList() as $user) {
            foreach ($categories as $category) {
                $this->createDatabaseEntry->checkForCategoryCompletion($user, $category);
            }
        }
    }
}
