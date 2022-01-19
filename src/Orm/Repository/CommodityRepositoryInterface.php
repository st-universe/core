<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CommodityInterface;

/**
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
    public function getAll(): array;
}
