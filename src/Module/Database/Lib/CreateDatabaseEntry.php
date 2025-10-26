<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseCategoryAwardRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class CreateDatabaseEntry implements CreateDatabaseEntryInterface
{
    public function __construct(
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private CreateUserAwardInterface $createUserAward,
        private DatabaseCategoryAwardRepositoryInterface $databaseCategoryAwardRepository
    ) {}

    #[\Override]
    public function createDatabaseEntryForUser(User $user, int $databaseEntryId): ?DatabaseEntry
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

            $this->checkForCategoryCompletion($user, $databaseEntry->getCategory(), $databaseEntryId, $databaseEntry->getLayerId());
        }

        return $databaseEntry;
    }

    public function checkForCategoryCompletion(
        User $user,
        DatabaseCategory $category,
        ?int $ignoredDatabaseEntryId = null,
        ?int $layerId = null
    ): void {
        if ($ignoredDatabaseEntryId === null && $layerId === null) {
            $layerIds = $this->databaseEntryRepository->getDistinctLayerIdsByCategory($category->getId());
            foreach ($layerIds as $currentLayerId) {
                if ($this->databaseUserRepository->hasUserCompletedCategoryAndLayer($user->getId(), $category->getId(), null, $currentLayerId)) {
                    $categoryAward = $this->databaseCategoryAwardRepository->findByCategoryIdAndLayerId($category->getId(), $currentLayerId);

                    if ($categoryAward !== null && $categoryAward->getAward() !== null) {
                        $this->createUserAward->createAwardForUser($user, $categoryAward->getAward());
                    }
                }
            }
        } elseif ($this->databaseUserRepository->hasUserCompletedCategoryAndLayer($user->getId(), $category->getId(), $ignoredDatabaseEntryId, $layerId)) {
            $categoryAward = $this->databaseCategoryAwardRepository->findByCategoryIdAndLayerId($category->getId(), $layerId);
            if ($categoryAward !== null && $categoryAward->getAward() !== null) {
                $this->createUserAward->createAwardForUser($user, $categoryAward->getAward());
            }
        }
    }
}
