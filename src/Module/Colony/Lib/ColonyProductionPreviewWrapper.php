<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

class ColonyProductionPreviewWrapper
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    /** @var array<ColonyProduction> */
    private array $production;

    /**
     * @param array<ColonyProduction> $production
     */
    function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        array $production
    ) {
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->production = $production;
    }

    /**
     * @param int|string $buildingId
     *
     * @return array<ColonyProduction>
     */
    public function __get($buildingId): array
    {
        return $this->getPreview((int) $buildingId);
    }

    /**
     * @return array<ColonyProduction>
     */
    private function getPreview(int $buildingId): array
    {
        $bcommodities = $this->buildingCommodityRepository->getByBuilding((int)$buildingId);
        $ret = [];
        foreach ($bcommodities as $commodityId => $prod) {
            $commodityId = $prod->getCommodityId();
            if (array_key_exists($commodityId, $this->production)) {
                $ret[$commodityId] = clone $this->production[$commodityId];
                $ret[$commodityId]->upperProduction($prod->getAmount());
            } else {
                $obj = new ColonyProduction();
                $obj->setCommodityId($commodityId);
                $obj->setProduction($prod->getAmount());
                $ret[$commodityId] = $obj;
            }
            $ret[$commodityId]->setPreviewProduction($prod->getAmount());
        }
        return $ret;
    }
}
