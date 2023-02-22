<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\Commodity;

/**
 * @extends EntityRepository<Commodity>
 */
final class CommodityRepository extends EntityRepository implements CommodityRepositoryInterface
{
    public function getByBuildingsOnColony(int $colonyId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Commodity::class, 'c');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'name', 'name');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT c.id,c.name,c.sort,c.view,c.type, c.npc_commodity FROM stu_commodity c WHERE c.id IN (
                            SELECT bg.commodity_id FROM stu_buildings_commodity bg WHERE bg.buildings_id IN (
                                SELECT cfd.buildings_id FROM stu_colonies_fielddata cfd WHERE cfd.colonies_id = :colonyId
                    )
                ) ORDER BY c.name ASC',
                $rsm
            )
            ->setParameter('colonyId', $colonyId)
            ->getResult();
    }

    public function getByType(int $typeId): array
    {
        return $this->findBy([
            'type' => $typeId,
        ], ['sort' => 'asc']);
    }

    public function getViewable(): array
    {
        return $this->findBy([
            'view' => true,
        ], ['sort' => 'asc']);
    }

    public function getTradeable(): array
    {
        return $this->findBy([
            'view' => true,
            'npc_commodity' => false,
            'type' => CommodityTypeEnum::COMMODITY_TYPE_STANDARD,
        ], ['sort' => 'asc']);
    }

    public function getAll(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    INDEX BY c.id',
                    Commodity::class
                )
            )
            ->getResult();
    }
}
