<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<CrewTraining>
 */
final class CrewTrainingRepository extends EntityRepository implements CrewTrainingRepositoryInterface
{
    #[\Override]
    public function save(CrewTraining $researched): void
    {
        $em = $this->getEntityManager();

        $em->persist($researched);
    }

    #[\Override]
    public function delete(CrewTraining $researched): void
    {
        $em = $this->getEntityManager();

        $em->remove($researched);
    }

    #[\Override]
    public function prototype(): CrewTraining
    {
        return new CrewTraining();
    }

    #[\Override]
    public function truncateByColony(Colony $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ct WHERE ct.colony = :colony',
                    CrewTraining::class
                )
            )
            ->setParameter('colony', $colony)
            ->execute();
    }

    #[\Override]
    public function getCountByUser(User $user): int
    {
        return $this->count([
            'user' => $user
        ]);
    }

    #[\Override]
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ct
                    FROM %s ct
                    WHERE MOD(ct.colony_id, :groupCount) + 1 = :groupId
                    AND ct.user_id != :idNoOne',
                    CrewTraining::class
                ),
            )
            ->setParameters([
                'groupId' => $batchGroup,
                'groupCount' => $batchGroupCount,
                'idNoOne' => UserConstants::USER_NOONE
            ])
            ->getResult();
    }
}
