<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\ColonyBuildingEffects;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingAction implements BuildingActionInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private BuildingManagerInterface $buildingManager,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyBuildingEffects $colonyBuildingEffects,
        private BuildingCommodityDeltaTracker $buildingCommodityDeltaTracker
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
            $this->buildingCommodityDeltaTracker->registerForBuilding($host, $building, 1);
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
            $this->buildingCommodityDeltaTracker->registerForBuilding($host, $building, -1);
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
            $this->buildingCommodityDeltaTracker->registerForBuilding($host, $building, -1);
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
            $this->colonyBuildingEffects->clearReactivationMarkers($field, $host);
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

    private function handleUndergroundLogisticsActivation(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, 1);
    }

    private function handleUndergroundLogisticsDeactivation(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, -1);
    }

    private function handleOrbitalMaintenanceDeactivation(
        Building $building,
        Colony $host,
        GameControllerInterface $game
    ): void {
        $deactivatedCount = $this->colonyBuildingEffects->deactivateOrbitalMaintenanceConsumers(
            $building,
            $host,
            function (PlanetField $field): void {
                $this->buildingManager->deactivate($field);
            },
            function (PlanetField $field): void {
                $this->buildingCommodityDeltaTracker->registerOnSuccessfulDeactivation($field);
            }
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
