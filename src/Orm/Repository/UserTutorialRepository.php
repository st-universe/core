<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<UserTutorial>
 */
final class UserTutorialRepository extends EntityRepository implements UserTutorialRepositoryInterface
{
    #[Override]
    public function prototype(): UserTutorialInterface
    {
        return new UserTutorial();
    }

    #[Override]
    public function save(UserTutorialInterface $userTutorial): void
    {
        $em = $this->getEntityManager();
        $em->persist($userTutorial);
        $em->flush();
    }

    #[Override]
    public function delete(UserTutorialInterface $userTutorial): void
    {
        $em = $this->getEntityManager();
        $em->remove($userTutorial);
        $em->flush();
    }

    #[Override]
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

    #[Override]
    public function findByUserAndViewContext(UserInterface $user, ViewContext $viewContext): ?UserTutorial
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ut FROM %s ut
                JOIN %s ts
                WITH ts.id = ut.tutorialStep
                WHERE ut.user = :user
                AND ts.module = :module
                AND ts.view = :view',
                UserTutorial::class,
                TutorialStep::class
            )
        )->setParameters([
            'user' => $user,
            'module' => $viewContext->getModule()->value,
            'view' => $viewContext->getViewIdentifier(),
        ])->getOneOrNullResult();
    }
}
