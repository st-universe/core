<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\ColonyShipRepairInterface;

/**
 * @extends EntityRepository<ColonyShipRepair>
 */
final class ColonyShipRepairRepository extends EntityRepository implements ColonyShipRepairRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyShipRepairInterface
    {
        return new ColonyShipRepair();
    }

    #[Override]
    public function getByColonyField(int $colonyId, int $fieldId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId,
            'field_id' => $fieldId
        ], ['id' => 'asc']);
    }

    #[Override]
    public function getByShip(int $shipId): ?ColonyShipRepairInterface
    {
        return $this->findOneBy([
            'ship_id' => $shipId
        ]);
    }

    #[Override]
    public function getMostRecentJobs(int $tickId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(ColonyShipRepair::class, 'c');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'colony_id', 'colony_id');
        $rsm->addFieldResult('c', 'ship_id', 'ship_id');
        $rsm->addFieldResult('c', 'field_id', 'field_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT  c.id, c.colony_id, c.ship_id, c.field_id
                FROM    (
                        SELECT  *, ROW_NUMBER() OVER (PARTITION BY colony_id, field_id ORDER BY id ASC) rn
                        FROM    stu_colonies_shiprepair
                        ) c
                WHERE   c.rn IN (1,2)',
                $rsm
            )
            ->setParameter('tickId', $tickId)
            ->getResult();
    }

    #[Override]
    public function save(ColonyShipRepairInterface $colonyShipRepair): void
    {
        $em = $this->getEntityManager();

        $em->persist($colonyShipRepair);
    }

    #[Override]
    public function delete(ColonyShipRepairInterface $colonyShipRepair): void
    {
        $em = $this->getEntityManager();

        $em->remove($colonyShipRepair);
        //$em->flush();
    }

    #[Override]
    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.ship_id = :shipId',
                ColonyShipRepair::class
            )
        );
        $q->setParameter('shipId', $shipId);
        $q->execute();
    }
}
