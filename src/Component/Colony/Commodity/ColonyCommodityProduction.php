<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

final class ColonyCommodityProduction implements ColonyCommodityProductionInterface
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ColonyInterface $colony;

    private ColonyLibFactoryInterface $colonyLibFactory;

    /**
     * @var array<int, CommodityInterface>
     */
    private array $commodityCache;

    /**
     * @param array<int, CommodityInterface> $commodityCache
     */
    public function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        ColonyInterface $colony,
        ColonyLibFactoryInterface $colonyLibFactory,
        array $commodityCache
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

            $commodity = $this->commodityCache[$commodityId];

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
