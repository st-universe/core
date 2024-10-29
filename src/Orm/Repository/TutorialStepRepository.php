<?php

namespace Stu\Orm\Repository;

use Couchbase\View;
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

        $subquery = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts.id
             FROM %s ts
             JOIN %s ut WITH ts.id = ut.tutorial_step_id
             WHERE ut.user = :user
             AND ts.module = :module
             AND ts.view = :view',
                TutorialStep::class,
                UserTutorial::class
            )
        )->setParameters([
            'user' => $user,
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
        ]);

        $result = $subquery->getOneOrNullResult();

        if (!$result) {
            return [];
        }


        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts FROM %s ts
             WHERE ts.module = :module
             AND ts.view = :view
             ORDER BY ts.sort ASC',
                TutorialStep::class
            )
        )->setParameters([
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
        ])->getResult();
    }

    public function findByViewContextAndSort(string $viewContext, int $sort): ?TutorialStep
    {
        return $this->getEntityManager()->createQuery(
            sprintf('SELECT ts FROM %s ts
             WHERE ts.view = :view
             AND ts.sort = :sort', TutorialStep::class)
        )->setParameters([
            'view' => $viewContext,
            'sort' => $sort
        ])->getOneOrNullResult();
    }
}