<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTutorial;

/**
 * @extends ObjectRepository<UserTutorial>
 */
interface UserTutorialRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserTutorial;

    public function save(UserTutorial $userTutorial): void;

    public function delete(UserTutorial $userTutorial): void;

    public function truncateByUser(User $user): void;

    public function findByUserAndViewContext(User $user, ViewControllerContext $viewContext): ?UserTutorial;
}
