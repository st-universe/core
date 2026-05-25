<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Building\ColonyBuildingEffects;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingActionEffects
{
    public function __construct(
        private BuildingManagerInterface $buildingManager,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyBuildingEffects $colonyBuildingEffects,
        private BuildingCommodityDeltaTracker $buildingCommodityDeltaTracker
    ) {}

    public function canActivateOnColony(Colony $host, Building $building, GameControllerInterface $game): bool
    {
        if (!$this->hasEnoughWorkers($host, $building)) {
            $game->getInfo()->addInformationf(
                _('Zum Aktivieren des Gebäudes (%s) werden %s Arbeiter benötigt'),
                $building->getName(),
                $building->getWorkers()
            );

            return false;
        }

        foreach ($this->getActivationCommodityRequirements() as [$commodityId, $commodityName]) {
            if (!$this->checkCommodityRequirement($host, $building, $commodityId, $commodityName, $game)) {
                return false;
            }
        }

        return true;
    }

    public function handleActivation(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, 1);
    }

    public function registerForBuilding(Colony $host, Building $building, int $delta): void
    {
        $this->buildingCommodityDeltaTracker->registerForBuilding($host, $building, $delta);
    }

    public function handleDeactivation(Building $building, Colony $host, GameControllerInterface $game): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, -1);
        $this->handleOrbitalMaintenanceDeactivation($building, $host, $game);
        $this->handleShipyardLogisticsDeactivation($building, $host, $game);
    }

    public function handleRemovalBeforeDemolition(
        PlanetField $field,
        Building $building,
        Colony $host,
        bool $isDueToUpgrade,
        GameControllerInterface $game
    ): void {
        if (!$isDueToUpgrade) {
            $this->colonyBuildingEffects->clearReactivationMarkers($field, $host);
            return;
        }

        if (!$field->isActive()) {
            return;
        }

        $this->handleOrbitalMaintenanceRemovalForUpgrade($field, $building, $host, $game);
    }

    private function hasEnoughWorkers(Colony $host, Building $building): bool
    {
        return $host->getChangeable()->getWorkless() >= $building->getWorkers();
    }

    /**
     * @return array<array{0: int, 1: string}>
     */
    private function getActivationCommodityRequirements(): array
    {
        return [
            [CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE, 'Orbitalwartung'],
            [CommodityTypeConstants::COMMODITY_EFFECT_SHIPYARD_LOGISTICS, 'Werftlogistik']
        ];
    }

    private function checkCommodityRequirement(
        Colony $host,
        Building $building,
        int $commodityId,
        string $commodityName,
        GameControllerInterface $game
    ): bool {
        $consumption = $this->buildingCommodityDeltaTracker->getBuildingCommodityAmount($building, $commodityId);
        if ($consumption >= 0) {
            return true;
        }

        $currentProduction = $this->buildingCommodityDeltaTracker->getProductionWithDelta($host, $commodityId);

        if ($currentProduction + $consumption >= 0) {
            return true;
        }

        $game->getInfo()->addInformationf(
            _('Das Gebäude (%s) kann nicht aktiviert werden, da nicht genug %s zur Verfügung steht'),
            $building->getName(),
            $commodityName
        );

        return false;
    }

    private function handleOrbitalMaintenanceDeactivation(
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $deactivatedCount = $this->deactivateCommodityConsumers(
            $building,
            $host,
            CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE
        );

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert'),
                $deactivatedCount
            );
        }
    }

    private function handleShipyardLogisticsDeactivation(
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $deactivatedCount = $this->deactivateCommodityConsumers(
            $building,
            $host,
            CommodityTypeConstants::COMMODITY_EFFECT_SHIPYARD_LOGISTICS
        );

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Werftgebäude deaktiviert'),
                $deactivatedCount
            );
        }
    }

    private function deactivateCommodityConsumers(
        Building $building,
        Colony $host,
        int $commodityId
    ): int {
        return $this->colonyBuildingEffects->deactivateCommodityConsumers(
            $building,
            $host,
            $commodityId,
            function (PlanetField $field): void {
                $this->buildingManager->deactivate($field);
            },
            function (PlanetField $field): void {
                $this->buildingCommodityDeltaTracker->registerOnSuccessfulDeactivation($field);
            }
        );
    }

    private function handleOrbitalMaintenanceRemovalForUpgrade(
        PlanetField $field,
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $reactivationId = $field->getId();
        $field->setReactivateAfterUpgrade($reactivationId);
        $this->planetFieldRepository->save($field);

        $deactivatedCount = $this->colonyBuildingEffects->deactivateOrbitalMaintenanceConsumers(
            $building,
            $host,
            function (PlanetField $consumerField): void {
                $this->buildingManager->deactivate($consumerField);
            },
            function (PlanetField $consumerField): void {
                $this->buildingCommodityDeltaTracker->registerOnSuccessfulDeactivation($consumerField);
            },
            $reactivationId
        );

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert und werden nach dem Upgrade reaktiviert'),
                $deactivatedCount
            );
        }
    }
}
