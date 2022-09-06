<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Component\Database\DatabaseCategoryTypeEnum;
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

    public function __construct(
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        UserAwardRepositoryInterface $userAwardRepository
    ) {
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->userAwardRepository = $userAwardRepository;
    }

    public function createDatabaseEntryForUser(UserInterface $user, int $databaseEntryId): ?DatabaseEntryInterface
    {
        if ($databaseEntryId === 0) {
            return null;
        }

        $databaseEntry = $this->databaseEntryRepository->find($databaseEntryId);

        if ($databaseEntry !== null) {
            $userEntry = $this->databaseUserRepository->prototype()
                ->setUser($user)
                ->setDatabaseEntry($databaseEntry)
                ->setDate(time());

            $this->databaseUserRepository->save($userEntry);
        }

        $this->checkForCompletion($user, $databaseEntry->getCategory()->getId());

        return $databaseEntry;
    }

    private function checkForCompletion(UserInterface $user, int $categoryId): void
    {
        if ($this->databaseUserRepository->hasUserCompletedCategory($user->getId(), $categoryId)) {

            //check if an award is configured for this category
            if (!array_key_exists($categoryId, DatabaseCategoryTypeEnum::CATEGORY_TO_AWARD)) {
                return;
            }

            $award = $this->userAwardRepository->prototype();
            $award->setUser($user);
            $award->setType(DatabaseCategoryTypeEnum::CATEGORY_TO_AWARD[$categoryId]);

            $this->userAwardRepository->save($award);
        }
    }
}
