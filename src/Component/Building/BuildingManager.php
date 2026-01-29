<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Manages actions relating to buildings on planets
 */
final class BuildingManager implements BuildingManagerInterface
{
    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly BuildingFunctionActionMapperInterface $buildingFunctionActionMapper,
        private readonly BuildingPostActionInterface $buildingPostAction
    ) {}

    #[\Override]
    public function activate(PlanetField $field): bool
    {
        $building = $field->getBuilding();

        if ($building === null) {
            return false;
        }

        if (!$field->isActivateable()) {
            return false;
        }

        if ($field->isActive()) {
            return true;
        }

        if ($field->hasHighDamage()) {
            return false;
        }

        $changeable = $this->getChangeable($field);

        $workerAmount = $building->getWorkers();

        if ($changeable instanceof ColonyChangeable) {
            $worklessAmount = $changeable->getWorkless();
            if ($worklessAmount < $workerAmount) {
                return false;
            }

            $changeable->setWorkless($worklessAmount - $workerAmount);
        }

        $changeable
            ->setWorkers($changeable->getWorkers() + $workerAmount)
            ->setMaxBev($changeable->getMaxBev() + $building->getHousing());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleActivation($building, $field->getHost());

        $this->saveHost($field->getHost());

        return true;
    }

    #[\Override]
    public function deactivate(PlanetField $field): void
    {
        $building = $field->getBuilding();

        if ($building === null) {
            return;
        }

        if (!$field->isActivateable()) {
            return;
        }

        if (!$field->isActive()) {
            return;
        }

        $changeable = $this->getChangeable($field);

        $this->updateWorkerAndMaxBev($building, $changeable);
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleDeactivation($building, $field->getHost());

        $this->saveHost($field->getHost());
    }

    private function saveHost(Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyRepository->save($host);
        } else {
            $this->colonySandboxRepository->save($host);
        }
    }

    private function updateWorkerAndMaxBev(Building $building, ColonyChangeable|ColonySandbox $host): void
    {
        $workerAmount = $building->getWorkers();

        if ($host instanceof ColonyChangeable) {
            $host->setWorkless($host->getWorkless() + $workerAmount);
        }
        $host->setWorkers($host->getWorkers() - $workerAmount);
        $host->setMaxBev($host->getMaxBev() - $building->getHousing());
    }

    #[\Override]
    public function remove(PlanetField $field, bool $isDueToUpgrade = false): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$isDueToUpgrade && !$building->isRemovable()) {
            return;
        }

        $host = $field->getHost();
        $changeable = $this->getChangeable($field);
        $wasActive = $field->isActive();

        if (!$field->isUnderConstruction()) {
            if ($wasActive && $host instanceof Colony) {
                $this->handleUndergroundLogisticsRemoval($field, $host);
                if (!$isDueToUpgrade) {
                    $this->handleOrbitalMaintenanceRemoval($field, $host);
                }
            }

            $this->deactivate($field);

            $shouldUpdateStorageAndEps = true;
            if ($this->buildingRequiresUndergroundLogistics($building)) {
                $shouldUpdateStorageAndEps = $this->hasUndergroundLogisticsProduction($host);
            }

            if ($shouldUpdateStorageAndEps) {
                $changeable
                    ->setMaxStorage($changeable->getMaxStorage() - $building->getStorage())
                    ->setMaxEps($changeable->getMaxEps() - $building->getEpsStorage());
            }
        }

        foreach ($building->getFunctions() as $function) {
            $buildingFunction = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunction);
            if ($handler !== null && $host instanceof Colony) {
                $handler->destruct($buildingFunction, $host);
            }
        }

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->saveHost($field->getHost());
    }

    #[\Override]
    public function finish(PlanetField $field, bool $activate = true): ?string
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return null;
        }

        $changeable = $this->getChangeable($field);

        $field
            ->setActive(0)
            ->setIntegrity($building->getIntegrity());

        $shouldReactivateOthers = $field->getReactivateAfterUpgrade() === $field->getId();

        $activationDetails = null;
        if ($building->isActivateAble()) {
            if ($activate) {
                $activationDetails = $this->activate($field)
                    ? '[color=green]Und konnte wunschgemäß aktiviert werden[/color]'
                    : '[color=red]Konnte allerdings nicht wie gewünscht aktiviert werden[/color]';
            } else {
                $activationDetails = 'Und wurde wunschgemäß nicht aktiviert';
            }
        }

        $shouldUpdateStorageAndEps = true;
        if ($this->buildingRequiresUndergroundLogistics($building)) {
            $host = $field->getHost();
            if (!$this->hasEnoughUndergroundLogistics($host, $building)) {
                $shouldUpdateStorageAndEps = false;
            }
        }

        if ($shouldUpdateStorageAndEps) {
            $changeable
                ->setMaxStorage($changeable->getMaxStorage() + $building->getStorage())
                ->setMaxEps($changeable->getMaxEps() + $building->getEpsStorage());
        }

        $this->saveHost($field->getHost());
        $this->planetFieldRepository->save($field);

        $reactivatedCount = 0;
        if ($shouldReactivateOthers) {
            $host = $field->getHost();
            if ($host instanceof Colony) {
                $field->setReactivateAfterUpgrade(null);
                $this->planetFieldRepository->save($field);
                $reactivatedCount = $this->reactivateOrbitalBuildingsAfterUpgrade($host, $field->getId());
            }
        }

        if ($reactivatedCount > 0) {
            $activationDetails .= sprintf(' - Es wurden %d Orbitalgebäude reaktiviert', $reactivatedCount);
        }

        return $activationDetails;
    }

    private function getChangeable(PlanetField $field): ColonyChangeable|ColonySandbox
    {
        $host = $field->getHost();

        return $host instanceof ColonySandbox
            ? $host
            : $host->getChangeable();
    }

    private function buildingRequiresUndergroundLogistics(Building $building): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $logisticsCommodityId && $buildingCommodity->getAmount() < 0) {
                return true;
            }
        }

        return false;
    }

    private function buildingProducesUndergroundLogistics(Building $building): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $logisticsCommodityId && $buildingCommodity->getAmount() > 0) {
                return true;
            }
        }

        return false;
    }

    private function hasUndergroundLogisticsProduction(Colony|ColonySandbox $host): bool
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

    private function hasEnoughUndergroundLogistics(Colony|ColonySandbox $host, Building $building): bool
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $production = 0;
        $consumption = 0;

        $producingFields = $this->planetFieldRepository->getCommodityProducingByHostAndCommodity($host, $logisticsCommodityId);
        foreach ($producingFields as $field) {
            if ($field->isActive() && $field->getBuilding() !== null) {
                foreach ($field->getBuilding()->getCommodities() as $commodity) {
                    if ($commodity->getCommodityId() === $logisticsCommodityId) {
                        $production += $commodity->getAmount();
                    }
                }
            }
        }

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity($host, $logisticsCommodityId, [1]);
        foreach ($consumingFields as $field) {
            if ($field->getBuilding() !== null) {
                foreach ($field->getBuilding()->getCommodities() as $commodity) {
                    if ($commodity->getCommodityId() === $logisticsCommodityId) {
                        $consumption += $commodity->getAmount();
                    }
                }
            }
        }

        $buildingConsumption = 0;
        foreach ($building->getCommodities() as $commodity) {
            if ($commodity->getCommodityId() === $logisticsCommodityId) {
                $buildingConsumption = $commodity->getAmount();
                break;
            }
        }

        return ($production + $consumption + $buildingConsumption) >= 0;
    }

    private function handleUndergroundLogisticsRemoval(PlanetField $field, Colony $host): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$this->buildingProducesUndergroundLogistics($building)) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $logisticsCommodityId,
            [0, 1]
        );

        $totalStorage = 0;
        $totalEps = 0;

        foreach ($consumingFields as $consumingField) {
            $consumingBuilding = $consumingField->getBuilding();
            if ($consumingBuilding === null) {
                continue;
            }

            $totalStorage += $consumingBuilding->getStorage();
            $totalEps += $consumingBuilding->getEpsStorage();
        }

        if ($totalStorage > 0 || $totalEps > 0) {
            $changeable = $host->getChangeable();
            $changeable
                ->setMaxStorage($changeable->getMaxStorage() - $totalStorage)
                ->setMaxEps($changeable->getMaxEps() - $totalEps);
        }
    }

    private function reactivateOrbitalBuildingsAfterUpgrade(Colony $host, int $upgradedFieldId): int
    {
        $fieldsToReactivate = array_filter(
            $host->getPlanetFields()->toArray(),
            fn(PlanetField $f) => $f->getReactivateAfterUpgrade() === $upgradedFieldId
        );

        $reactivatedCount = 0;

        foreach ($fieldsToReactivate as $fieldToReactivate) {
            if ($this->activate($fieldToReactivate)) {
                $reactivatedCount++;
            }
            $fieldToReactivate->setReactivateAfterUpgrade(null);
            $this->planetFieldRepository->save($fieldToReactivate);
        }

        return $reactivatedCount;
    }

    private function handleOrbitalMaintenanceRemoval(PlanetField $field, Colony $host): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        $maintenanceCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;
        $producesMaintenance = false;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $maintenanceCommodityId && $buildingCommodity->getAmount() > 0) {
                $producesMaintenance = true;
                break;
            }
        }

        if (!$producesMaintenance) {
            return;
        }

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $maintenanceCommodityId,
            [1]
        );

        foreach ($consumingFields as $consumingField) {
            if ($consumingField->isActive()) {
                $this->deactivate($consumingField);
            }
        }
    }
}
