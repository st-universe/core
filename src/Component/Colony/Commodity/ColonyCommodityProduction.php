<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

final class ColonyCommodityProduction implements ColonyCommodityProductionInterface
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ColonyInterface $colony;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CommodityCacheInterface $commodityCache;

    public function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        ColonyInterface $colony,
        ColonyLibFactoryInterface $colonyLibFactory,
        CommodityCacheInterface $commodityCache
    ) {
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->colony = $colony;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->commodityCache = $commodityCache;
    }

    public function getProduction(): array
    {
        $result = $this->buildingCommodityRepository->getProductionByColony(
            $this->colony->getId(),
            $this->colony->getColonyClass()->getId()
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
