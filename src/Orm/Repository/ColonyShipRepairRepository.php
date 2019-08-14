<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\ColonyShipRepairInterface;

final class ColonyShipRepairRepository extends EntityRepository
    implements
    ColonyShipRepairRepositoryInterface
{

    public function prototype(): ColonyShipRepairInterface
    {
        return new ColonyShipRepair();
    }

    public function getByColonyField(int $colonyId, int $fieldId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId,
            'field_id' => $fieldId
        ]);
    }

    public function getMostRecentJobs(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t WHERE t.id IN (SELECT MIN(a.id) FROM %s a GROUP BY a.colony_id, a.field_id)',
                ColonyShipRepair::class,
                ColonyShipRepair::class,
            )
        )->getResult();
    }

    public function save(ColonyShipRepairInterface $colonyShipRepair): void
    {
        $em = $this->getEntityManager();

        $em->persist($colonyShipRepair);
        $em->flush();
    }

    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.ship_id = %d',
                ColonyShipRepair::class,
                $shipId
            )
        );
        $q->execute();
    }
}