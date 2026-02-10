<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

final class BuildingCommodityDeltaTracker
{
    /** @var array<int, array<int, int>> */
    private array $commodityProductionDeltas = [];

    public function __construct(private readonly ColonyLibFactoryInterface $colonyLibFactory) {}

    public function getProductionWithDelta(Colony $host, int $commodityId): int
    {
        $production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
        $baseProduction = array_key_exists($commodityId, $production)
            ? $production[$commodityId]->getProduction()
            : 0;

        return $baseProduction + $this->getCommodityProductionDelta($host->getId(), $commodityId);
    }

    public function registerForBuilding(Colony $host, Building $building, int $factor): void
    {
        $hostId = $host->getId();

        foreach ($building->getCommodities() as $buildingCommodity) {
            $commodityId = $buildingCommodity->getCommodityId();
            $amount = $buildingCommodity->getAmount();

            if ($amount === 0) {
                continue;
            }

            if (!array_key_exists($hostId, $this->commodityProductionDeltas)) {
                $this->commodityProductionDeltas[$hostId] = [];
            }

            if (!array_key_exists($commodityId, $this->commodityProductionDeltas[$hostId])) {
                $this->commodityProductionDeltas[$hostId][$commodityId] = 0;
            }

            $this->commodityProductionDeltas[$hostId][$commodityId] += $amount * $factor;
        }
    }

    public function getBuildingCommodityAmount(Building $building, int $commodityId): int
    {
        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $commodityId) {
                return $buildingCommodity->getAmount();
            }
        }

        return 0;
    }

    public function registerOnSuccessfulDeactivation(PlanetField $field): void
    {
        $host = $field->getHost();
        $building = $field->getBuilding();
        if (!$host instanceof Colony || $building === null || $field->isActive()) {
            return;
        }

        $this->registerForBuilding($host, $building, -1);
    }

    private function getCommodityProductionDelta(int $hostId, int $commodityId): int
    {
        return $this->commodityProductionDeltas[$hostId][$commodityId] ?? 0;
    }
}

