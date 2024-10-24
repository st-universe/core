<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStepInterface;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<TutorialStep>
 * 
 * @method null|TutorialStepInterface find(integer $id)
 */
interface TutorialStepRepositoryInterface extends ObjectRepository
{
    /** @return array<int, TutorialStepInterface> */
    public function findByUserAndViewContext(UserInterface $user, ViewContext $viewContext): array;
}
