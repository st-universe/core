<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CommodityInterface;

/**
 * @extends ObjectRepository<Commodity>
 *
 * @method null|CommodityInterface find(integer $commodityId)
 */
interface CommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return CommodityInterface[]
     */
    public function getByBuildingsOnColony(int $colonyId): array;

    /**
     * @return CommodityInterface[]
     */
    public function getByType(int $typeId): array;

    /**
     * @return CommodityInterface[]
     */
    public function getViewable(): array;

	    /**
     * @return CommodityInterface[]
     */
    public function getTradeable(): array;

    /**
     * Returns a dict of all commodities, indexed by it's id
     *
     * @return array<int, CommodityInterface>
     */
    public function getAll(): array;
}
