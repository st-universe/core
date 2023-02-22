<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

final class ColonyCommodityProduction implements ColonyCommodityProductionInterface
{
    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyInterface $colony;

    public function __construct(
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyInterface $colony
    ) {
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->colonyLibFactory = $colonyLibFactory;
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
            if (($data['gc'] + $data['pc']) != 0) {
                $production[(int) $data['commodity_id']] = $this->colonyLibFactory->createColonyProduction($data);
            }
        }

        return $production;
    }
}
