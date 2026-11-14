<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Override;
use Stu\Orm\Entity\RumpAssimilateable;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftRump;

final class BorgAssimilationRepository implements BorgAssimilationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Override]
    public function isAssimilateable(SpacecraftRump $rump): bool
    {
        return $this->em->find(RumpAssimilateable::class, $rump->getId()) !== null;
    }

    #[Override]
    public function getBySpacecraft(Spacecraft $spacecraft): ?SpacecraftAssimilated
    {
        return $this->getBySpacecraftId($spacecraft->getId());
    }

    #[Override]
    public function getBySpacecraftId(int $id): ?SpacecraftAssimilated
    {
        return $this->em->find(SpacecraftAssimilated::class, $id);
    }

    #[Override]
    public function getActive(): array
    {
        return $this->em->createQuery('SELECT a FROM ' . SpacecraftAssimilated::class . ' a WHERE a.startedAt IS NOT NULL AND a.source IS NOT NULL AND a.completedAt IS NULL')
            ->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[Override]
    public function getBySource(Spacecraft $source): array
    {
        return $this->em->getRepository(SpacecraftAssimilated::class)->findBy(['source' => $source]);
    }

    #[Override]
    public function save(SpacecraftAssimilated $assimilation): void
    {
        $this->em->persist($assimilation);
    }

    #[Override]
    public function delete(SpacecraftAssimilated $assimilation): void
    {
        $assimilation->getSpacecraft()->setAssimilation(null);
        $this->em->remove($assimilation);
    }

    #[Override]
    public function lock(Spacecraft $spacecraft): void
    {
        $this->em->lock($spacecraft, LockMode::PESSIMISTIC_WRITE);
    }

    #[Override]
    public function deleteByRump(SpacecraftRump $rump): void
    {
        $entry = $this->em->find(RumpAssimilateable::class, $rump->getId());
        if ($entry !== null) {
            $this->em->remove($entry);
        }
    }
}
