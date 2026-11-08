<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;

final class UserModuleOptimizationRepository implements UserModuleOptimizationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Override]
    public function get(User $user, Module $module): ?UserModuleOptimization
    {
        return $this->entityManager->getRepository(UserModuleOptimization::class)->findOneBy(['user' => $user, 'module' => $module]);
    }

    #[Override]
    public function save(UserModuleOptimization $progress): void
    {
        $this->entityManager->persist($progress);
    }

    #[Override]
    public function deleteByUser(User $user): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . UserModuleOptimization::class . ' p WHERE p.user = :user')
            ->setParameter('user', $user)->execute();
    }
}
