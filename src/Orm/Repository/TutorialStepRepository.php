<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserTutorial;

/**
 * @extends EntityRepository<TutorialStep>
 */
final class TutorialStepRepository extends EntityRepository implements TutorialStepRepositoryInterface
{
    public function findByUserAndViewContext(UserInterface $user, ViewContext $viewContext): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts FROM %s ts INDEX BY ts.id
                JOIN %s ut
                WITH ts.id = ut.tutorial_step_id
                WHERE ts.module = :module
                AND ts.view = :view
                AND ut.user = :user
                ORDER BY ts.sort ASC',
                TutorialStep::class,
                UserTutorial::class
            )
        )->setParameters([
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
            'user' => $user,
        ])->getResult();
    }
}
