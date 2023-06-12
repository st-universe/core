<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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
     * @return list<CommodityInterface>
     */
    public function getByBuildingsOnColony(int $colonyId): array;

    /**
     * @return list<CommodityInterface>
     */
    public function getByType(int $typeId): array;

    /**
     * @return list<CommodityInterface>
     */
    public function getViewable(): array;

    /**
     * @return list<CommodityInterface>
     */
    public function getTradeable(): array;

    /**
     * Returns a dict of all commodities, indexed by its id
     *
     * @return array<int, CommodityInterface>
     */
    public function getAll(): array;
}
