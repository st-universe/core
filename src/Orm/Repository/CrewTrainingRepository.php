<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\CrewTrainingInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<CrewTraining>
 */
final class CrewTrainingRepository extends EntityRepository implements CrewTrainingRepositoryInterface
{
    public function save(CrewTrainingInterface $researched): void
    {
        $em = $this->getEntityManager();

        $em->persist($researched);
    }

    public function delete(CrewTrainingInterface $researched): void
    {
        $em = $this->getEntityManager();

        $em->remove($researched);
    }

    public function prototype(): CrewTrainingInterface
    {
        return new CrewTraining();
    }

    public function truncateByColony(ColonyInterface $colony): void
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

    public function getCountByUser(UserInterface $user): int
    {
        return $this->count([
            'user' => $user
        ]);
    }

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
                'idNoOne' => UserEnum::USER_NOONE
            ])
            ->getResult();
    }
}
