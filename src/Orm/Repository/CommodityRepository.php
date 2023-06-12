<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\PlanetField;

/**
 * @extends EntityRepository<Commodity>
 */
final class CommodityRepository extends EntityRepository implements CommodityRepositoryInterface
{
    public function getByBuildingsOnColony(int $colonyId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    WHERE c.id IN (
                        SELECT bg.commodity_id
                        FROM %s bg
                        WHERE bg.buildings_id IN (
                            SELECT cfd.buildings_id
                            FROM %s cfd
                            WHERE cfd.colonies_id = :colonyId
                        )
                    ) ORDER BY c.name ASC',
                    Commodity::class,
                    BuildingCommodity::class,
                    PlanetField::class
                )
            )
            ->setParameter('colonyId', $colonyId)
            ->getResult();
    }

    public function getByType(int $typeId): array
    {
        return $this->findBy([
            'type' => $typeId
        ], ['sort' => 'asc']);
    }

    public function getViewable(): array
    {
        return $this->findBy([
            'view' => true
        ], ['sort' => 'asc']);
    }

    public function getTradeable(): array
    {
        return $this->findBy([
            'view' => true,
            'npc_commodity' => false,
            'type' => CommodityTypeEnum::COMMODITY_TYPE_STANDARD
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
