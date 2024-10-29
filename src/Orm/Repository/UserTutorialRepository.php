<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<UserTutorial>
 */
final class UserTutorialRepository extends EntityRepository implements UserTutorialRepositoryInterface
{
    public function prototype(): UserTutorialInterface
    {
        return new UserTutorial();
    }

    public function save(UserTutorialInterface $userTutorial): void
    {
        $em = $this->getEntityManager();
        $em->persist($userTutorial);
        $em->flush();
    }

    public function delete(UserTutorialInterface $userTutorial): void
    {
        $em = $this->getEntityManager();
        $em->remove($userTutorial);
        $em->flush();
    }

    /**
     * @return UserTutorialInterface[]
     */
    public function findByUser(UserInterface $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function truncateByUser(UserInterface $user): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ut WHERE ut.user = :user',
                    UserTutorial::class
                )
            )
            ->setParameters([
                'user' => $user
            ])
            ->execute();
    }


    public function truncateByUserAndStepId(UserInterface $user, int $stepId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ut
                    WHERE ut.user = :user
                    AND ut.tutorial_step_id = :stepId',
                    UserTutorial::class
                )
            )
            ->setParameters([
                'user' => $user,
                'stepId' => $stepId
            ])
            ->execute();
    }

    public function findUserTutorialByUserAndViewContext(UserInterface $user, ViewContext $viewContext): ?UserTutorial
    {
        $tutorialSteps = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts.id FROM %s ts
             WHERE ts.module = :module
             AND ts.view = :view',
                TutorialStep::class
            )
        )->setParameters([
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
        ])->getResult();

        if (empty($tutorialSteps)) {
            return null;
        }


        $stepIds = array_map(fn($step) => $step['id'], $tutorialSteps);


        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ut FROM %s ut
             JOIN %s ts WITH ut.tutorial_step_id = ts.id
             WHERE ut.user = :user
             AND ut.tutorial_step_id IN (:stepIds)',
                UserTutorial::class,
                TutorialStep::class
            )
        )->setParameters([
            'user' => $user,
            'stepIds' => $stepIds,
        ])->getOneOrNullResult();
    }

    public function findUserTutorialByUserAndView(UserInterface $user, string $view): ?UserTutorial
    {
        $tutorialSteps = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts.id FROM %s ts
             WHERE ts.view = :view',
                TutorialStep::class
            )
        )->setParameters([
            'view' => $view,
        ])->getResult();

        if (empty($tutorialSteps)) {
            return null;
        }


        $stepIds = array_map(fn($step) => $step['id'], $tutorialSteps);


        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ut FROM %s ut
             JOIN %s ts WITH ut.tutorial_step_id = ts.id
             WHERE ut.user = :user
             AND ut.tutorial_step_id IN (:stepIds)',
                UserTutorial::class,
                TutorialStep::class
            )
        )->setParameters([
            'user' => $user,
            'stepIds' => $stepIds,
        ])->getOneOrNullResult();
    }
}