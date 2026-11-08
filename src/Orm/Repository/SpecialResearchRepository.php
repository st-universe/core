<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Override;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

final class SpecialResearchRepository implements SpecialResearchRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Override]
    public function getByUser(User $user): array
    {
        return $this->entityManager->createQuery('SELECT r FROM ' . SpecialResearch::class . ' r WHERE r.user = :user')
            ->setParameter('user', $user)->setHint(Query::HINT_REFRESH, true)->getResult();
    }

    #[Override]
    public function getRunning(): array
    {
        return $this->entityManager->createQuery(
            'SELECT r FROM ' . SpecialResearch::class . ' r WHERE r.status = :status'
        )->setParameter('status', SpecialResearchStatusEnum::RUNNING)
            ->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[Override]
    public function getRunningByField(PlanetField $field): array
    {
        return $this->entityManager->createQuery(
            'SELECT r FROM ' . SpecialResearch::class . ' r WHERE r.status = :status AND r.fieldId = :field'
        )->setParameters(['status' => SpecialResearchStatusEnum::RUNNING, 'field' => $field->getId()])->getResult();
    }

    #[Override]
    public function lockStorage(Colony $colony, PlanetField $field): void
    {
        $this->entityManager->lock($colony->getUser(), LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->lock($colony, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->refresh($colony);
        $this->entityManager->lock($field, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->refresh($field);
        $this->entityManager->createQuery('SELECT s FROM ' . Storage::class . ' s WHERE s.colony = :colony')
            ->setParameter('colony', $colony)->setHint(Query::HINT_REFRESH, true)
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[Override]
    public function save(SpecialResearch $research): void
    {
        $this->entityManager->persist($research);
    }

    #[Override]
    public function deleteByUser(User $user): void
    {
        new TechnologyRepository($this->entityManager)->deleteByUser($user);
        $this->entityManager->createQuery('DELETE FROM ' . SpecialResearch::class . ' r WHERE r.user = :user')
            ->setParameter('user', $user)->execute();
    }

    #[Override]
    public function detachColony(Colony $colony): void
    {
        new TechnologyRepository($this->entityManager)->detachColony($colony);
        foreach ($this->entityManager->getRepository(SpecialResearch::class)->findBy(['colony' => $colony]) as $research) {
            $research->setColony(null);
        }
    }
}
