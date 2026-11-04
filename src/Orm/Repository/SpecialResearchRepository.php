<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

final class SpecialResearchRepository implements SpecialResearchRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->entityManager->createQuery('SELECT r FROM ' . SpecialResearch::class . ' r WHERE r.user = :user')
            ->setParameter('user', $user)->setHint(Query::HINT_REFRESH, true)->getResult();
    }

    #[\Override]
    public function getDue(int $timestamp): array
    {
        return $this->entityManager->createQuery(
            'SELECT r FROM ' . SpecialResearch::class . ' r WHERE r.status = :status AND r.finishTime <= :now'
        )->setParameters(['status' => SpecialResearchStatusEnum::RUNNING, 'now' => $timestamp])
            ->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[\Override]
    public function lockStorage(Colony $colony): void
    {
        $this->entityManager->lock($colony->getUser(), LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->lock($colony, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->refresh($colony);
        $this->entityManager->createQuery('SELECT s FROM ' . Storage::class . ' s WHERE s.colony = :colony')
            ->setParameter('colony', $colony)->setHint(Query::HINT_REFRESH, true)
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[\Override]
    public function save(SpecialResearch $research): void
    {
        $this->entityManager->persist($research);
    }

    #[\Override]
    public function deleteByUser(User $user): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . SpecialResearch::class . ' r WHERE r.user = :user')
            ->setParameter('user', $user)->execute();
    }

    #[\Override]
    public function detachColony(Colony $colony): void
    {
        foreach ($this->entityManager->getRepository(SpecialResearch::class)->findBy(['colony' => $colony]) as $research) {
            $research->setColony(null);
        }
    }
}
