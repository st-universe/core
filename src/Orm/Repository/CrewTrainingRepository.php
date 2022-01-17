<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\CrewTrainingInterface;

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
        //$em->flush();
    }

    public function prototype(): CrewTrainingInterface
    {
        return new CrewTraining();
    }

    public function truncateByColony(int $colonyId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s t WHERE t.colony_id = :colonyId',
                    CrewTraining::class
                )
            )
            ->setParameter('colonyId', $colonyId)
            ->execute();
    }

    public function getCountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
    }

    public function getByTick(int $tickId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(CrewTraining::class, 'ct');
        $rsm->addFieldResult('ct', 'id', 'id');
        $rsm->addFieldResult('ct', 'colony_id', 'colony_id');
        $rsm->addFieldResult('ct', 'user_id', 'user_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT ct.id,ct.colony_id,ct.user_id FROM stu_crew_training ct WHERE ct.user_id IN (
                    SELECT u.id FROM stu_user u WHERE u.id != :idNoOne AND tick = :tickId
                )',
                $rsm
            )
            ->setParameters([
                'idNoOne' => GameEnum::USER_NOONE,
                'tickId' => $tickId
            ])
            ->getResult();
    }
}
