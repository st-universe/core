<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Control\ViewContext;
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

    public function truncateByUser(UserInterface $user): void;

    public function findByUserAndViewContext(UserInterface $user, ViewContext $viewContext): ?UserTutorial;
}
