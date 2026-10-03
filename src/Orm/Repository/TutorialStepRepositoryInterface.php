<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<TutorialStep>
 *
 * @method null|TutorialStep find(integer $id)
 */
interface TutorialStepRepositoryInterface extends ObjectRepository
{
    /** @return array<int, TutorialStep> */
    public function findByUserAndViewContext(User $user, ViewControllerContext $viewContext): array;

    /** @return array<TutorialStep> */
    public function findAllFirstSteps(): array;
}
