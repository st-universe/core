<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

class ColonyProductionPreviewWrapper
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;


    /** @var array<ColonyProduction> */
    private array $production = [];

    /** @var array<int, array<ColonyProduction>> */
    private array $preview = [];

    /**
     * @param array<ColonyProduction> $production
     */
    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory,
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        array $production
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
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
        $bid = (int) $buildingId;

        if (!array_key_exists($bid, $this->preview)) {
            $this->preview[$bid] = $this->loadPreview($bid);
        }

        return $this->preview[$bid];
    }

    /** @return array<ColonyProduction> */
    private function loadPreview(int $buildingId): array
    {
        $bcommodities = $this->buildingCommodityRepository->getByBuilding($buildingId);

        /** @var array<ColonyProduction> */
        $ret = [];
        foreach ($bcommodities as $commodityId => $prod) {
            $commodityId = $prod->getCommodityId();
            if (array_key_exists($commodityId, $this->production)) {

                $ret[$commodityId] = clone $this->production[$commodityId];
                $ret[$commodityId]->upperProduction($prod->getAmount());
            } else {

                $obj = $this->colonyLibFactory->createColonyProduction(
                    $prod->getCommodity(),
                    $prod->getAmount()
                );
                $ret[$commodityId] = $obj;
            }

            $ret[$commodityId]->setPreviewProduction($prod->getAmount());
        }

        return $ret;
    }
}
