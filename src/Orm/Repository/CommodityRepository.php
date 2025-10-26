<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\PlanetField;

/**
 * @extends EntityRepository<Commodity>
 */
final class CommodityRepository extends EntityRepository implements CommodityRepositoryInterface
{
    #[\Override]
    public function getByBuildingsOnColony(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    WHERE c.id IN (
                        SELECT bg.commodity_id
                        FROM %s bg
                        WHERE bg.buildings_id IN (
                            SELECT pf.buildings_id
                            FROM %s pf
                            WHERE pf.%s = :hostId
                        )
                    ) ORDER BY c.name ASC',
                    Commodity::class,
                    BuildingCommodity::class,
                    PlanetField::class,
                    $host->getHostType()->getPlanetFieldHostColumnIdentifier()
                )
            )
            ->setParameter('hostId', $host->getId())
            ->getResult();
    }

    #[\Override]
    public function getByType(int $typeId): array
    {
        return $this->findBy([
            'type' => $typeId
        ], ['sort' => 'asc']);
    }

    #[\Override]
    public function getViewable(): array
    {
        return $this->findBy([
            'view' => true
        ], ['sort' => 'asc']);
    }

    #[\Override]
    public function getTradeable(): array
    {
        return $this->findBy([
            'view' => true,
            'npc_commodity' => false,
            'type' => CommodityTypeConstants::COMMODITY_TYPE_STANDARD
        ], ['sort' => 'asc']);
    }

    #[\Override]
    public function getTradeableNPC(): array
    {
        return $this->findBy([
            'view' => true,
            'type' => CommodityTypeConstants::COMMODITY_TYPE_STANDARD
        ], ['sort' => 'asc']);
    }

    #[\Override]
    public function getAll(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    INDEX BY c.id
                    ORDER BY c.sort ASC',
                    Commodity::class
                )
            )
            ->getResult();
    }
}
