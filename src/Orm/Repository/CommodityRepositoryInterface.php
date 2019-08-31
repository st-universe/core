<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\CommodityInterface;

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
}