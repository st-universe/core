<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Override;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class CreateDatabaseEntry implements CreateDatabaseEntryInterface
{
    public function __construct(
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private CreateUserAwardInterface $createUserAward
    ) {}

    #[Override]
    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface
    {
        if ($databaseEntryId === 0) {
            return null;
        }

        if (!$user->hasColony()) {
            return null;
        }

        $databaseEntry = $this->databaseEntryRepository->find($databaseEntryId);

        if ($databaseEntry === null) {
            return null;
        }

        //create new user entry
        $userEntry = $this->databaseUserRepository->prototype()
            ->setUser($user)
            ->setDatabaseEntry($databaseEntry)
            ->setDate(time());

        $this->databaseUserRepository->save($userEntry);


        if (!$user->isNpc()) {
            //create prestige log
            $this->createPrestigeLog->createLogForDatabaseEntry($databaseEntry, $user, $userEntry->getDate());

            $this->checkForCategoryCompletion($user, $databaseEntry->getCategory(), $databaseEntryId);
        }

        return $databaseEntry;
    }

    public function checkForCategoryCompletion(
        UserInterface $user,
        DatabaseCategoryInterface $category,
        ?int $ignoredDatabaseEntryId = null
    ): void {
        //check if an award is configured for this category
        if ($category->getAward() === null) {
            return;
        }

        $award = $category->getAward();

        if ($this->databaseUserRepository->hasUserCompletedCategory($user->getId(), $category->getId(), $ignoredDatabaseEntryId)) {
            $this->createUserAward->createAwardForUser($user, $award);
        }
    }
}
