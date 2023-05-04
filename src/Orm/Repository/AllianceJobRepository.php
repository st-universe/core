<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<AllianceJob>
 */
final class AllianceJobRepository extends EntityRepository implements AllianceJobRepositoryInterface
{
    public function prototype(): AllianceJobInterface
    {
        return new AllianceJob();
    }

    public function save(AllianceJobInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(AllianceJobInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    public function getByAlliance(int $allianceId): array
    {
        return $this->findBy([
            'alliance_id' => $allianceId,
        ]);
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s aj WHERE aj.user_id = :userId',
                AllianceJob::class
            )
        )->setParameters([
            'userId' => $userId,
        ])->execute();
    }

    public function truncateByAlliance(int $allianceId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s aj WHERE aj.alliance_id = :allianceId',
                AllianceJob::class
            )
        )->setParameters([
            'allianceId' => $allianceId,
        ])->execute();
    }

    public function getByAllianceAndType(int $allianceId, int $typeId): array
    {
        return $this->findBy([
            'alliance_id' => $allianceId,
            'type' => $typeId,
        ]);
    }

    public function getByUserAndAllianceAndType(
        UserInterface $user,
        AllianceInterface $alliance,
        int $type
    ): ?AllianceJobInterface {
        return $this->findOneBy([
            'user' => $user,
            'alliance' => $alliance,
            'type' => $type,
        ]);
    }

    public function getSingleResultByAllianceAndType(int $allianceId, int $typeId): ?AllianceJobInterface
    {
        return $this->findOneBy([
            'alliance_id' => $allianceId,
            'type' => $typeId,
        ]);
    }

    public function truncateAllAllianceJobs(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s aj',
                AllianceJob::class
            )
        )->execute();
    }
}
