<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

final class ColonyCommodityProduction implements ColonyCommodityProductionInterface
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ColonyInterface $colony;

    public function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        ColonyInterface $colony
    ) {
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->colony = $colony;
    }

    public function getProduction(): array
    {
        $result = $this->buildingCommodityRepository->getProductionByColony(
            $this->colony->getId(),
            $this->colony->getColonyClass()->getId()
        );

        $production = [];
        foreach ($result as $data) {
            if ($data->getProduction() != 0) {
                $production[$data->getCommodityId()] = $data;
            }
        }

        return $production;
    }
}
