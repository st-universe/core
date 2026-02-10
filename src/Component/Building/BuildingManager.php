<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
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

        if (!$this->canRemoveBuilding($building, $isDueToUpgrade)) {
            return;
        }

        $host = $field->getHost();
        if (!$field->isUnderConstruction()) {
            $this->handleRemovalWhenNotUnderConstruction($field, $building, $host, $isDueToUpgrade);
        }

        $this->destructBuildingFunctions($building, $host);

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

        $host = $field->getHost();
        $shouldReactivateOthers = $this->initializeFinishedField($field, $building);
        $shouldApplyUndergroundLogisticsActivation = $this->shouldApplyUndergroundLogisticsActivationAfterFinish(
            $host,
            $building
        );

        [$activationDetails, $wasActivated] = $this->createFinishActivationResult($field, $building, $activate);

        $this->updateStorageAndEpsAfterFinish($field, $building);
        if ($shouldApplyUndergroundLogisticsActivation && $wasActivated && $host instanceof Colony) {
            $this->handleUndergroundLogisticsActivation($building, $host);
        }

        $this->saveHost($field->getHost());
        $this->planetFieldRepository->save($field);

        $reactivatedCount = $shouldReactivateOthers
            ? $this->reactivateFieldsAfterUpgrade($field, $wasActivated)
            : 0;
        return $this->appendReactivationDetails($activationDetails, $reactivatedCount);
    }

    private function canRemoveBuilding(Building $building, bool $isDueToUpgrade): bool
    {
        return $isDueToUpgrade || $building->isRemovable();
    }

    private function handleRemovalWhenNotUnderConstruction(
        PlanetField $field,
        Building $building,
        Colony|ColonySandbox $host,
        bool $isDueToUpgrade
    ): void {
        if ($field->isActive() && $host instanceof Colony) {
            $this->handleUndergroundLogisticsRemoval($building, $host);
            if (!$isDueToUpgrade) {
                $this->handleOrbitalMaintenanceRemoval($building, $host);
            }
        }

        $this->deactivate($field);
        $this->updateStorageAndEpsAfterRemoval($field, $building, $host);
    }

    private function updateStorageAndEpsAfterRemoval(
        PlanetField $field,
        Building $building,
        Colony|ColonySandbox $host
    ): void {
        if (!$this->shouldUpdateStorageAndEpsAfterRemoval($host, $building)) {
            return;
        }

        $this->adjustStorageAndEps(
            $this->getChangeable($field),
            -$building->getStorage(),
            -$building->getEpsStorage()
        );
    }

    private function shouldUpdateStorageAndEpsAfterRemoval(Colony|ColonySandbox $host, Building $building): bool
    {
        if (!$this->buildingRequiresUndergroundLogistics($building)) {
            return true;
        }

        return $this->hasUndergroundLogisticsProduction($host);
    }

    private function destructBuildingFunctions(Building $building, Colony|ColonySandbox $host): void
    {
        foreach ($building->getFunctions() as $function) {
            $buildingFunction = $function->getFunction();
            $handler = $this->buildingFunctionActionMapper->map($buildingFunction);

            if ($handler !== null && $host instanceof Colony) {
                $handler->destruct($buildingFunction, $host);
            }
        }
    }

    private function initializeFinishedField(PlanetField $field, Building $building): bool
    {
        $field
            ->setActive(0)
            ->setIntegrity($building->getIntegrity());

        return $field->getReactivateAfterUpgrade() === $field->getId();
    }

    /**
     * @return array{0: ?string, 1: bool}
     */
    private function createFinishActivationResult(
        PlanetField $field,
        Building $building,
        bool $activate
    ): array
    {
        if (!$building->isActivateAble()) {
            return [null, false];
        }

        if (!$activate) {
            return ['Und wurde wunschgemäß nicht aktiviert', false];
        }

        $wasActivated = $this->activate($field);

        return [
            $wasActivated
                ? '[color=green]Und konnte wunschgemäß aktiviert werden[/color]'
                : '[color=red]Konnte allerdings nicht wie gewünscht aktiviert werden[/color]',
            $wasActivated
        ];
    }

    private function shouldApplyUndergroundLogisticsActivationAfterFinish(
        Colony|ColonySandbox $host,
        Building $building
    ): bool
    {
        if (!$host instanceof Colony) {
            return false;
        }

        if (!$this->buildingProducesUndergroundLogistics($building)) {
            return false;
        }

        return !$this->hasUndergroundLogisticsProduction($host);
    }

    private function updateStorageAndEpsAfterFinish(PlanetField $field, Building $building): void
    {
        $host = $field->getHost();
        if (!$this->shouldUpdateStorageAndEpsAfterFinish($host, $building)) {
            return;
        }

        $this->adjustStorageAndEps(
            $this->getChangeable($field),
            $building->getStorage(),
            $building->getEpsStorage()
        );
    }

    private function shouldUpdateStorageAndEpsAfterFinish(Colony|ColonySandbox $host, Building $building): bool
    {
        if (!$this->buildingRequiresUndergroundLogistics($building)) {
            return true;
        }

        return $this->hasEnoughUndergroundLogistics($host, $building);
    }

    private function reactivateFieldsAfterUpgrade(PlanetField $field, bool $wasActivated): int
    {
        $host = $field->getHost();
        $upgradedFieldId = $field->getId();

        $field->setReactivateAfterUpgrade(null);
        $this->planetFieldRepository->save($field);

        if (!$host instanceof Colony) {
            return 0;
        }

        if (!$wasActivated) {
            $this->clearReactivationMarkersForFieldId($host, $upgradedFieldId);
            return 0;
        }

        return $this->reactivateOrbitalBuildingsAfterUpgrade($host, $upgradedFieldId);
    }

    private function appendReactivationDetails(?string $activationDetails, int $reactivatedCount): ?string
    {
        if ($reactivatedCount <= 0) {
            return $activationDetails;
        }

        return (string) $activationDetails . sprintf(
            ' - Es wurden %d Orbitalgebäude reaktiviert',
            $reactivatedCount
        );
    }

    private function adjustStorageAndEps(ColonyChangeable|ColonySandbox $changeable, int $storageDelta, int $epsDelta): void
    {
        if ($storageDelta === 0 && $epsDelta === 0) {
            return;
        }

        $changeable
            ->setMaxStorage($changeable->getMaxStorage() + $storageDelta)
            ->setMaxEps($changeable->getMaxEps() + $epsDelta);
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
        return $this->buildingProducesCommodity(
            $building,
            CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS
        );
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

    /**
     * @param array<PlanetField> $fields
     */
    private function deactivateActiveFields(array $fields): void
    {
        foreach ($fields as $field) {
            if ($field->isActive()) {
                $this->deactivate($field);
            }
        }
    }

    private function handleUndergroundLogisticsRemoval(Building $building, Colony $host): void
    {
        if (!$this->buildingProducesUndergroundLogistics($building)) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $logisticsCommodityId,
            [0, 1]
        );

        [$totalStorage, $totalEps] = $this->sumStorageAndEps($consumingFields);
        $this->adjustStorageAndEps($host->getChangeable(), -$totalStorage, -$totalEps);
    }

    private function handleUndergroundLogisticsActivation(Building $building, Colony $host): void
    {
        if (!$this->buildingProducesUndergroundLogistics($building)) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $logisticsCommodityId,
            [0, 1]
        );

        [$totalStorage, $totalEps] = $this->sumStorageAndEps($consumingFields);
        $this->adjustStorageAndEps($host->getChangeable(), $totalStorage, $totalEps);
    }

    /**
     * @return array<PlanetField>
     */
    private function getFieldsMarkedForReactivation(Colony $host, int $upgradedFieldId): array
    {
        return array_filter(
            $host->getPlanetFields()->toArray(),
            fn(PlanetField $f) => $f->getReactivateAfterUpgrade() === $upgradedFieldId
        );
    }

    private function clearReactivationMarkersForFieldId(Colony $host, int $upgradedFieldId): void
    {
        foreach ($this->getFieldsMarkedForReactivation($host, $upgradedFieldId) as $fieldToClear) {
            $fieldToClear->setReactivateAfterUpgrade(null);
            $this->planetFieldRepository->save($fieldToClear);
        }
    }

    private function reactivateOrbitalBuildingsAfterUpgrade(Colony $host, int $upgradedFieldId): int
    {
        $fieldsToReactivate = $this->getFieldsMarkedForReactivation($host, $upgradedFieldId);
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

    private function handleOrbitalMaintenanceRemoval(Building $building, Colony $host): void
    {
        $maintenanceCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;
        if (!$this->buildingProducesCommodity($building, $maintenanceCommodityId)) {
            return;
        }

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $maintenanceCommodityId,
            [1]
        );

        $this->deactivateActiveFields($consumingFields);
    }
}

