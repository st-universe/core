<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class CreateDatabaseEntry implements CreateDatabaseEntryInterface
{
    private DatabaseEntryRepositoryInterface $databaseEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private CreateUserAwardInterface $createUserAward;

    public function __construct(
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        CreatePrestigeLogInterface $createPrestigeLog,
        CreateUserAwardInterface $createUserAward
    ) {
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->createUserAward = $createUserAward;
    }

    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface
    {
        if ($databaseEntryId === 0) {
            return null;
        }

        if (
            $user->getState() === UserEnum::USER_STATE_COLONIZATION_SHIP
            || $user->getState() === UserEnum::USER_STATE_UNCOLONIZED
        ) {
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

        $award = $category->getAward();

        if ($this->databaseUserRepository->hasUserCompletedCategory($user->getId(), $category->getId())) {
            $this->createUserAward->createAwardForUser($user, $award);
        }
    }
}
