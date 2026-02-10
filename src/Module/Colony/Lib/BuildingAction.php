<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingAction implements BuildingActionInterface
{
    /** @var array<int, array<int, int>> */
    private array $commodityProductionDeltas = [];

    public function __construct(
        private StorageManagerInterface $storageManager,
        private BuildingManagerInterface $buildingManager,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private PlanetFieldRepositoryInterface $planetFieldRepository
    ) {}

    #[\Override]
    public function activate(PlanetField $field, GameControllerInterface $game): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$this->canActivateField($field, $building, $game)) {
            return;
        }

        $host = $field->getHost();
        if ($host instanceof Colony) {
            if (!$this->canActivateOnColony($host, $building, $game)) {
                return;
            }

            $this->handleUndergroundLogisticsActivation($building, $host);
        }

        if (!$this->buildingManager->activate($field)) {
            return;
        }

        if ($host instanceof Colony) {
            $this->registerCommodityDeltaForBuilding($host, $building, 1);
        }

        $this->addActivationInformation($field, $building, $game);
    }

    #[\Override]
    public function deactivate(PlanetField $field, GameControllerInterface $game): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$this->canDeactivateField($field)) {
            return;
        }

        $host = $field->getHost();
        if ($host instanceof Colony) {
            $this->handleUndergroundLogisticsDeactivation($building, $host);
            $this->handleOrbitalMaintenanceDeactivation($building, $host, $game);
        }

        $this->buildingManager->deactivate($field);

        if ($host instanceof Colony && !$field->isActive()) {
            $this->registerCommodityDeltaForBuilding($host, $building, -1);
        }

        $this->addDeactivationInformation($field, $building, $game);
    }

    #[\Override]
    public function remove(
        PlanetField $field,
        GameControllerInterface $game,
        bool $isDueToUpgrade = false
    ): void {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$this->canRemoveBuilding($building, $isDueToUpgrade)) {
            return;
        }

        $host = $field->getHost();
        $wasActive = $field->isActive();
        if ($host instanceof Colony) {
            $this->handleColonyRemovalBeforeDemolition($field, $building, $host, $isDueToUpgrade, $game);
        }

        $this->buildingManager->remove($field, $isDueToUpgrade);

        if ($host instanceof Colony && $wasActive && !$field->isActive()) {
            $this->registerCommodityDeltaForBuilding($host, $building, -1);
        }

        $this->addRemovalInformation($field, $building, $game);

        if ($host instanceof Colony) {
            $this->recycleBuildingCosts($building, $host, $game);
        }
    }

    private function canActivateField(PlanetField $field, Building $building, GameControllerInterface $game): bool
    {
        if (!$field->isActivateable()) {
            return false;
        }
        if ($field->isActive()) {
            return false;
        }
        if (!$field->hasHighDamage()) {
            return true;
        }

        $game->getInfo()->addInformationf(
            _('Das Gebäude (%s) kann aufgrund zu starker Beschädigung nicht aktiviert werden'),
            $building->getName()
        );

        return false;
    }

    private function canDeactivateField(PlanetField $field): bool
    {
        return $field->isActivateable() && $field->isActive();
    }

    private function canActivateOnColony(Colony $host, Building $building, GameControllerInterface $game): bool
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

    private function canRemoveBuilding(Building $building, bool $isDueToUpgrade): bool
    {
        return $isDueToUpgrade || $building->isRemovable();
    }

    private function handleColonyRemovalBeforeDemolition(
        PlanetField $field,
        Building $building,
        Colony $host,
        bool $isDueToUpgrade,
        GameControllerInterface $game
    ): void {
        if (!$isDueToUpgrade) {
            $this->clearReactivationMarkers($field, $host);
            return;
        }

        if (!$field->isActive()) {
            return;
        }

        $this->handleOrbitalMaintenanceRemovalForUpgrade($field, $building, $host, $game);
    }

    private function recycleBuildingCosts(Building $building, Colony $host, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformation(_('Es konnten folgende Waren recycled werden'));

        foreach ($building->getCosts() as $cost) {
            $amount = $this->getRecyclableAmount($host, $cost->getHalfAmount());
            if ($amount <= 0) {
                $game->getInfo()->addInformation(_('[b][color=#ff2626]Keine weiteren Lagerkapazitäten vorhanden![/color][/b]'));
                break;
            }

            $commodity = $cost->getCommodity();
            $this->storageManager->upperStorage($host, $commodity, $amount);
            $game->getInfo()->addInformationf('%d %s', $amount, $commodity->getName());
        }
    }

    private function getRecyclableAmount(Colony $host, int $halfAmount): int
    {
        $currentStorage = $host->getStorageSum();
        $maxStorage = $host->getMaxStorage();

        if ($currentStorage + $halfAmount > $maxStorage) {
            return $maxStorage - $currentStorage;
        }

        return $halfAmount;
    }

    private function addActivationInformation(PlanetField $field, Building $building, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformationf(
            _('%s auf Feld %s wurde aktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    private function addDeactivationInformation(PlanetField $field, Building $building, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformationf(
            _('%s auf Feld %s wurde deaktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    private function addRemovalInformation(PlanetField $field, Building $building, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    private function checkCommodityRequirement(
        Colony $host,
        Building $building,
        int $commodityId,
        string $commodityName,
        GameControllerInterface $game
    ): bool {
        $consumption = $this->getBuildingCommodityAmount($building, $commodityId);
        if ($consumption >= 0) {
            return true;
        }

        $currentProduction = $this->getCommodityProductionWithDelta($host, $commodityId);

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

    private function getCommodityProductionWithDelta(Colony $host, int $commodityId): int
    {
        $production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
        $baseProduction = array_key_exists($commodityId, $production)
            ? $production[$commodityId]->getProduction()
            : 0;

        return $baseProduction + $this->getCommodityProductionDelta($host->getId(), $commodityId);
    }

    private function getCommodityProductionDelta(int $hostId, int $commodityId): int
    {
        return $this->commodityProductionDeltas[$hostId][$commodityId] ?? 0;
    }

    private function registerCommodityDeltaForBuilding(Colony $host, Building $building, int $factor): void
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

    private function getBuildingCommodityAmount(Building $building, int $commodityId): int
    {
        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $commodityId) {
                return $buildingCommodity->getAmount();
            }
        }

        return 0;
    }

    private function handleUndergroundLogisticsActivation(Building $building, Colony $host): void
    {
        $this->adjustUndergroundLogisticsCapacity($building, $host, 1);
    }

    private function handleUndergroundLogisticsDeactivation(Building $building, Colony $host): void
    {
        $this->adjustUndergroundLogisticsCapacity($building, $host, -1);
    }

    private function adjustUndergroundLogisticsCapacity(Building $building, Colony $host, int $direction): void
    {
        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;
        if (!$this->buildingProducesCommodity($building, $logisticsCommodityId)) {
            return;
        }

        [$totalStorage, $totalEps] = $this->sumStorageAndEps(
            $this->getCommodityConsumingFields($host, $logisticsCommodityId, [0, 1])
        );

        $this->adjustStorageAndEps($host, $totalStorage * $direction, $totalEps * $direction);
    }

    private function handleOrbitalMaintenanceDeactivation(
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $maintenanceCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;
        if (!$this->buildingProducesCommodity($building, $maintenanceCommodityId)) {
            return;
        }

        $deactivatedCount = $this->deactivateActiveFields(
            $this->getCommodityConsumingFields($host, $maintenanceCommodityId, [1])
        );

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert'),
                $deactivatedCount
            );
        }
    }

    private function handleOrbitalMaintenanceRemovalForUpgrade(
        PlanetField $field,
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $maintenanceCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;
        if (!$this->buildingProducesCommodity($building, $maintenanceCommodityId)) {
            return;
        }

        $reactivationId = $field->getId();
        $field->setReactivateAfterUpgrade($reactivationId);
        $this->planetFieldRepository->save($field);

        $deactivatedCount = $this->deactivateActiveFields(
            $this->getCommodityConsumingFields($host, $maintenanceCommodityId, [1]),
            $reactivationId
        );

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert und werden nach dem Upgrade reaktiviert'),
                $deactivatedCount
            );
        }
    }

    /**
     * @param array<PlanetField> $fields
     */
    private function deactivateActiveFields(array $fields, ?int $reactivationId = null): int
    {
        $deactivatedCount = 0;

        foreach ($fields as $field) {
            if (!$field->isActive()) {
                continue;
            }

            $this->buildingManager->deactivate($field);

            $host = $field->getHost();
            $building = $field->getBuilding();
            if ($host instanceof Colony && $building !== null && !$field->isActive()) {
                $this->registerCommodityDeltaForBuilding($host, $building, -1);
            }

            if ($reactivationId !== null) {
                $field->setReactivateAfterUpgrade($reactivationId);
                $this->planetFieldRepository->save($field);
            }

            $deactivatedCount++;
        }

        return $deactivatedCount;
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

    /**
     * @param array<int> $state
     *
     * @return array<PlanetField>
     */
    private function getCommodityConsumingFields(Colony $host, int $commodityId, array $state): array
    {
        return $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $commodityId,
            $state
        );
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

    private function adjustStorageAndEps(Colony $host, int $storageDelta, int $epsDelta): void
    {
        if ($storageDelta === 0 && $epsDelta === 0) {
            return;
        }

        $changeable = $host->getChangeable();
        $changeable
            ->setMaxStorage($changeable->getMaxStorage() + $storageDelta)
            ->setMaxEps($changeable->getMaxEps() + $epsDelta);
    }

    private function clearReactivationMarkers(PlanetField $field, Colony $host): void
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
}
