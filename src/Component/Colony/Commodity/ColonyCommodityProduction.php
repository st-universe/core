<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

final class ColonyCommodityProduction implements ColonyCommodityProductionInterface
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private PlanetFieldHostInterface $host;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CommodityCacheInterface $commodityCache;

    public function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        PlanetFieldHostInterface $host,
        ColonyLibFactoryInterface $colonyLibFactory,
        CommodityCacheInterface $commodityCache
    ) {
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->host = $host;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->commodityCache = $commodityCache;
    }

    public function getProduction(): array
    {
        $result = $this->buildingCommodityRepository->getProductionByColony(
            $this->host,
            $this->host->getColonyClass()
        );

        $production = [];
        foreach ($result as $data) {
            $commodityId = $data['commodity_id'];

            $commodity = $this->commodityCache->get($commodityId);

            $colonyProduction = $this->colonyLibFactory->createColonyProduction(
                $commodity,
                $data['production'],
                $data['pc']
            );

            if ($colonyProduction->getProduction() != 0) {
                $production[$commodityId] = $colonyProduction;
            }
        }

        return $production;
    }
}
