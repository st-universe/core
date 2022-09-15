<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\UserAwardRepositoryInterface;

final class CreateDatabaseEntry implements CreateDatabaseEntryInterface
{
    private DatabaseEntryRepositoryInterface $databaseEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private UserAwardRepositoryInterface $userAwardRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        UserAwardRepositoryInterface $userAwardRepository,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->userAwardRepository = $userAwardRepository;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface
    {
        if ($databaseEntryId === 0) {
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


        if ($user->getId() > 100) {

            //create prestige log
            $this->createPrestigeLog->createLogForDatabaseEntry($databaseEntry, $user, $userEntry->getDate());

            $this->checkForCompletion($user, $databaseEntry->getCategory());
        }

        return $databaseEntry;
    }

    private function checkForCompletion(UserInterface $user, DatabaseCategoryInterface $category): void
    {
        //check if an award is configured for this category
        if ($category->getAward() === null) {
            return;
        }

        if ($this->databaseUserRepository->hasUserCompletedCategory($user->getId(), $category->getId())) {

            $userAward = $this->userAwardRepository->prototype();
            $userAward->setUser($user);
            $userAward->setAward($category->getAward());

            $this->userAwardRepository->save($userAward);
        }
    }
}
