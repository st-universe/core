<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Entity\UserInterface;

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

    public function truncateByUserAndModule(UserInterface $user, string $module): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE ut FROM %s ut
                    WHERE t.user = :user
                    AND EXISTS (SELECT ts FROM %s ts
                                WHERE ts.id = ut.tutorial_step_id
                                AND ts.module = :module)',
                    UserTutorial::class,
                    TutorialStep::class
                )
            )
            ->setParameters([
                'user' => $user,
                'module' => $module
            ])
            ->execute();
    }
}
