<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTutorial;

/**
 * @extends EntityRepository<TutorialStep>
 */
final class TutorialStepRepository extends EntityRepository implements TutorialStepRepositoryInterface
{
    #[Override]
    public function findByUserAndViewContext(User $user, ViewContext $viewContext): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts FROM %s ts INDEX BY ts.id
                WHERE EXISTS (SELECT ut FROM %s ut
                                JOIN %1$s ts2
                                WITH ts2.id = ut.tutorialStep
                                WHERE ut.user = :user
                                AND ts2.module = :module
                                AND ts.view = :view)',
                TutorialStep::class,
                UserTutorial::class
            )
        )->setParameters([
            'user' => $user,
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
        ])->getResult();
    }


    #[Override]
    public function findAllFirstSteps(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ts FROM %1$s ts
                    WHERE NOT EXISTS (SELECT ts2.id FROM %1$s ts2
                                        WHERE ts2.next_step_id = ts.id)',
                    TutorialStep::class
                )
            )
            ->getResult();
    }
}
