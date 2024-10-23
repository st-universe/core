<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<UserTutorial>
 */
interface UserTutorialRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserTutorialInterface;

    public function save(UserTutorialInterface $userTutorial): void;

    public function delete(UserTutorialInterface $userTutorial): void;

    /**
     * @return UserTutorialInterface[]
     */
    public function findByUser(UserInterface $user): array;

    /**
     * @param UserInterface $user
     * @param string $module
     * @return UserTutorialInterface|null
     */
    public function findByUserAndModule(UserInterface $user, string $module): ?UserTutorialInterface;

    public function truncateByUserAndModule(UserInterface $user, string $module): void;
}
