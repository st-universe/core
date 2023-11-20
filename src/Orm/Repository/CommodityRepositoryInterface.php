<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CommodityInterface;

/**
 * @extends ObjectRepository<Commodity>
 *
 * @method null|CommodityInterface find(integer $commodityId)
 * @method CommodityInterface[] findAll()
 */
interface CommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<CommodityInterface>
     */
    public function getByBuildingsOnColony(PlanetFieldHostInterface $host): array;

    /**
     * @return array<CommodityInterface>
     */
    public function getByType(int $typeId): array;

    /**
     * @return array<CommodityInterface>
     */
    public function getViewable(): array;

    /**
     * @return array<CommodityInterface>
     */
    public function getTradeable(): array;

    /**
     * Returns a dict of all commodities, indexed by its id
     *
     * @return array<int, CommodityInterface>
     */
    public function getAll(): array;
}
