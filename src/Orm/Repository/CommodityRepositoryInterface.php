<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Commodity;

/**
 * @extends ObjectRepository<Commodity>
 *
 * @method null|Commodity find(integer $commodityId)
 * @method Commodity[] findAll()
 */
interface CommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Commodity>
     */
    public function getByBuildingsOnColony(PlanetFieldHostInterface $host): array;

    /**
     * @return array<Commodity>
     */
    public function getByType(int $typeId): array;

    /**
     * @return array<Commodity>
     */
    public function getViewable(): array;

    /**
     * @return array<Commodity>
     */
    public function getTradeable(): array;

    /**
     * @return array<Commodity>
     */
    public function getTradeableNPC(): array;

    /**
     * Returns a dict of all commodities, indexed by its id
     *
     * @return array<int, Commodity>
     */
    public function getAll(): array;
}
