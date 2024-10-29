<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Module\Control\ViewContext;

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

    public function truncateByUserAndStepId(UserInterface $user, int $stepId): void;

    public function findUserTutorialByUserAndViewContext(UserInterface $user, ViewContext $viewContext): ?UserTutorial;

    public function findUserTutorialByUserAndView(UserInterface $user, string $view): ?UserTutorial;
}