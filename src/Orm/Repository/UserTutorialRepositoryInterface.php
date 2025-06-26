<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<UserTutorial>
 */
interface UserTutorialRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserTutorial;

    public function save(UserTutorial $userTutorial): void;

    public function delete(UserTutorial $userTutorial): void;

    public function truncateByUser(User $user): void;

    public function findByUserAndViewContext(User $user, ViewContext $viewContext): ?UserTutorial;
}
