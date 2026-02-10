<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyBuildingEffects
{
    public function __construct(private readonly PlanetFieldRepositoryInterface $planetFieldRepository) {}

    public function buildingRequiresUndergroundLogistics(Building $building): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $logisticsCommodityId && $buildingCommodity->getAmount() < 0) {
                return true;
            }
        }

        return false;
    }

    public function buildingProducesUndergroundLogistics(Building $building): bool
    {
        return $this->buildingProducesCommodity(
            $building,
            CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS
        );
    }

    public function hasUndergroundLogisticsProduction(Colony|ColonySandbox $host): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $producingFields = $this->planetFieldRepository->getCommodityProducingByHostAndCommodity($host, $logisticsCommodityId);

        foreach ($producingFields as $field) {
            if ($field->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasEnoughUndergroundLogistics(Colony|ColonySandbox $host, Building $building): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $production = $this->sumCommodityAmountFromFields(
            $this->planetFieldRepository->getCommodityProducingByHostAndCommodity($host, $logisticsCommodityId),
            $logisticsCommodityId,
            true
        );
        $consumption = $this->sumCommodityAmountFromFields(
            $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity($host, $logisticsCommodityId, [1]),
            $logisticsCommodityId
        );
        $buildingConsumption = $this->getFirstCommodityAmount($building, $logisticsCommodityId);

        return $production + $consumption + $buildingConsumption >= 0;
    }

    public function adjustUndergroundLogisticsCapacity(Building $building, Colony $host, int $direction): void
    {
        if (!$this->buildingProducesUndergroundLogistics($building)) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        [$totalStorage, $totalEps] = $this->sumStorageAndEps(
            $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
                $host,
                $logisticsCommodityId,
                [0, 1]
            )
        );

        $this->adjustStorageAndEps($host->getChangeable(), $totalStorage * $direction, $totalEps * $direction);
    }

    /**
     * @param callable(PlanetField):void $deactivateField
     * @param callable(PlanetField):void|null $afterDeactivate
     */
    public function deactivateOrbitalMaintenanceConsumers(
        Building $building,
        Colony $host,
        callable $deactivateField,
        ?callable $afterDeactivate = null,
        ?int $reactivationId = null
    ): int {
        $maintenanceCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;
        if (!$this->buildingProducesCommodity($building, $maintenanceCommodityId)) {
            return 0;
        }

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $maintenanceCommodityId,
            [1]
        );

        $deactivatedCount = 0;

        foreach ($consumingFields as $field) {
            if (!$field->isActive()) {
                continue;
            }

            $deactivateField($field);

            if ($reactivationId !== null) {
                $field->setReactivateAfterUpgrade($reactivationId);
                $this->planetFieldRepository->save($field);
            }

            if ($afterDeactivate !== null) {
                $afterDeactivate($field);
            }

            $deactivatedCount++;
        }

        return $deactivatedCount;
    }

    public function clearReactivationMarkers(PlanetField $field, Colony $host): void
    {
        $reactivationId = $field->getReactivateAfterUpgrade();
        if ($reactivationId === null) {
            return;
        }

        if ($reactivationId !== $field->getId()) {
            $this->clearSingleReactivationMarker($field);
            return;
        }

        $this->clearReactivationMarkersById($host, $reactivationId);
    }

    private function clearSingleReactivationMarker(PlanetField $field): void
    {
        $field->setReactivateAfterUpgrade(null);
        $this->planetFieldRepository->save($field);
    }

    private function clearReactivationMarkersById(Colony $host, int $reactivationId): void
    {
        foreach ($host->getPlanetFields() as $planetField) {
            if ($planetField->getReactivateAfterUpgrade() === $reactivationId) {
                $planetField->setReactivateAfterUpgrade(null);
                $this->planetFieldRepository->save($planetField);
            }
        }
    }

    private function buildingProducesCommodity(Building $building, int $commodityId): bool
    {
        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $commodityId && $buildingCommodity->getAmount() > 0) {
                return true;
            }
        }

        return false;
    }

    private function adjustStorageAndEps(ColonyChangeable $changeable, int $storageDelta, int $epsDelta): void
    {
        if ($storageDelta === 0 && $epsDelta === 0) {
            return;
        }

        $changeable
            ->setMaxStorage($changeable->getMaxStorage() + $storageDelta)
            ->setMaxEps($changeable->getMaxEps() + $epsDelta);
    }

    /**
     * @param array<PlanetField> $fields
     */
    private function sumCommodityAmountFromFields(array $fields, int $commodityId, bool $onlyActive = false): int
    {
        $amount = 0;

        foreach ($fields as $field) {
            if ($onlyActive && !$field->isActive()) {
                continue;
            }

            $building = $field->getBuilding();
            if ($building === null) {
                continue;
            }

            $amount += $this->sumCommodityAmount($building, $commodityId);
        }

        return $amount;
    }

    private function sumCommodityAmount(Building $building, int $commodityId): int
    {
        $amount = 0;

        foreach ($building->getCommodities() as $commodity) {
            if ($commodity->getCommodityId() === $commodityId) {
                $amount += $commodity->getAmount();
            }
        }

        return $amount;
    }

    private function getFirstCommodityAmount(Building $building, int $commodityId): int
    {
        foreach ($building->getCommodities() as $commodity) {
            if ($commodity->getCommodityId() === $commodityId) {
                return $commodity->getAmount();
            }
        }

        return 0;
    }

    /**
     * @param array<PlanetField> $fields
     *
     * @return array{0: int, 1: int}
     */
    private function sumStorageAndEps(array $fields): array
    {
        $totalStorage = 0;
        $totalEps = 0;

        foreach ($fields as $field) {
            $building = $field->getBuilding();
            if ($building === null) {
                continue;
            }

            $totalStorage += $building->getStorage();
            $totalEps += $building->getEpsStorage();
        }

        return [$totalStorage, $totalEps];
    }
}

