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
        if (!$field->isActivateAble()) {
            return;
        }
        if ($field->isActive()) {
            return;
        }
        if ($field->hasHighDamage()) {
            $game->getInfo()->addInformationf(
                _('Das Gebäude (%s) kann aufgrund zu starker Beschädigung nicht aktiviert werden'),
                $building->getName()
            );
            return;
        }

        $host = $field->getHost();
        if (
            $host instanceof Colony
            && $host->getChangeable()->getWorkless() < $building->getWorkers()
        ) {
            $game->getInfo()->addInformationf(
                _('Zum Aktivieren des Gebäudes (%s) werden %s Arbeiter benötigt'),
                $building->getName(),
                $building->getWorkers()
            );
            return;
        }

        if ($host instanceof Colony) {
            if (!$this->checkCommodityRequirement(
                $host,
                $building,
                CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE,
                'Orbitalwartung',
                $game
            )) {
                return;
            }

            if (!$this->checkCommodityRequirement(
                $host,
                $building,
                CommodityTypeConstants::COMMODITY_EFFECT_SHIPYARD_LOGISTICS,
                'Werftlogistik',
                $game
            )) {
                return;
            }

            $this->handleUndergroundLogisticsActivation($field, $host);
        }

        $this->buildingManager->activate($field);

        $game->getInfo()->addInformationf(
            _('%s auf Feld %s wurde aktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    #[\Override]
    public function deactivate(PlanetField $field, GameControllerInterface $game): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }
        if (!$field->isActivateAble()) {
            return;
        }
        if (!$field->isActive()) {
            return;
        }

        $host = $field->getHost();
        if ($host instanceof Colony) {
            $this->handleUndergroundLogisticsDeactivation($field, $host);
            $this->handleOrbitalMaintenanceDeactivation($field, $host, $game);
        }

        $this->buildingManager->deactivate($field);

        $game->getInfo()->addInformationf(
            _('%s auf Feld %s wurde deaktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
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

        if (!$isDueToUpgrade && !$building->isRemovable()) {
            return;
        }

        $host = $field->getHost();

        if (!$isDueToUpgrade && $host instanceof Colony) {
            $this->clearReactivationMarkers($field, $host);
        }

        if ($host instanceof Colony && $field->isActive()) {
            $this->handleOrbitalMaintenanceRemovalForUpgrade($field, $host, $isDueToUpgrade, $game);
        }

        $this->buildingManager->remove($field, $isDueToUpgrade);

        $game->getInfo()->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );


        if ($host instanceof ColonySandbox) {
            return;
        }

        $game->getInfo()->addInformation(_('Es konnten folgende Waren recycled werden'));

        foreach ($building->getCosts() as $value) {
            $halfAmount = $value->getHalfAmount();
            if ($host->getStorageSum() + $halfAmount > $host->getMaxStorage()) {
                $amount = $host->getMaxStorage() - $host->getStorageSum();
            } else {
                $amount = $halfAmount;
            }
            if ($amount <= 0) {
                $game->getInfo()->addInformation(_('[b][color=#ff2626]Keine weiteren Lagerkapazitäten vorhanden![/color][/b]'));
                break;
            }
            $this->storageManager->upperStorage($host, $value->getCommodity(), $amount);

            $game->getInfo()->addInformationf('%d %s', $amount, $value->getCommodity()->getName());
        }
    }

    private function checkCommodityRequirement(
        Colony $host,
        Building $building,
        int $commodityId,
        string $commodityName,
        GameControllerInterface $game
    ): bool {
        $consumption = 0;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $commodityId) {
                $consumption = $buildingCommodity->getAmount();
                break;
            }
        }

        if ($consumption < 0) {
            $production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
            $currentProduction = array_key_exists($commodityId, $production)
                ? $production[$commodityId]->getProduction()
                : 0;
            $projectedProduction = $currentProduction + $consumption;

            if ($projectedProduction < 0) {
                $game->getInfo()->addInformationf(
                    _('Das Gebäude (%s) kann nicht aktiviert werden, da nicht genug %s zur Verfügung steht'),
                    $building->getName(),
                    $commodityName
                );
                return false;
            }
        }

        return true;
    }

    private function handleUndergroundLogisticsActivation(PlanetField $field, Colony $host): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;
        $producesLogistics = false;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $logisticsCommodityId && $buildingCommodity->getAmount() > 0) {
                $producesLogistics = true;
                break;
            }
        }

        if (!$producesLogistics) {
            return;
        }

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
                ->setMaxStorage($changeable->getMaxStorage() + $totalStorage)
                ->setMaxEps($changeable->getMaxEps() + $totalEps);
        }
    }

    private function handleUndergroundLogisticsDeactivation(PlanetField $field, Colony $host): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS;
        $producesLogistics = false;

        foreach ($building->getCommodities() as $buildingCommodity) {
            if ($buildingCommodity->getCommodityId() === $logisticsCommodityId && $buildingCommodity->getAmount() > 0) {
                $producesLogistics = true;
                break;
            }
        }

        if (!$producesLogistics) {
            return;
        }

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

    private function handleOrbitalMaintenanceDeactivation(PlanetField $field, Colony $host, GameControllerInterface $game): void
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

        $deactivatedCount = 0;

        foreach ($consumingFields as $consumingField) {
            if ($consumingField->isActive()) {
                $this->buildingManager->deactivate($consumingField);
                $deactivatedCount++;
            }
        }

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert'),
                $deactivatedCount
            );
        }
    }

    private function handleOrbitalMaintenanceRemovalForUpgrade(PlanetField $field, Colony $host, bool $isDueToUpgrade, GameControllerInterface $game): void
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

        if (!$producesMaintenance || !$isDueToUpgrade) {
            return;
        }

        $field->setReactivateAfterUpgrade($field->getId());
        $this->planetFieldRepository->save($field);

        $consumingFields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $host,
            $maintenanceCommodityId,
            [1]
        );

        $deactivatedCount = 0;

        foreach ($consumingFields as $consumingField) {
            if ($consumingField->isActive()) {
                $this->buildingManager->deactivate($consumingField);
                $consumingField->setReactivateAfterUpgrade($field->getId());
                $this->planetFieldRepository->save($consumingField);
                $deactivatedCount++;
            }
        }

        if ($deactivatedCount > 0) {
            $game->getInfo()->addInformationf(
                _('Es wurden %d Orbitalgebäude deaktiviert und werden nach dem Upgrade reaktiviert'),
                $deactivatedCount
            );
        }
    }
    private function clearReactivationMarkers(PlanetField $field, Colony $host): void
    {
        $reactivationId = $field->getReactivateAfterUpgrade();

        if ($reactivationId === null) {
            return;
        }

        if ($reactivationId === $field->getId()) {
            foreach ($host->getPlanetFields() as $planetField) {
                if ($planetField->getReactivateAfterUpgrade() === $reactivationId) {
                    $planetField->setReactivateAfterUpgrade(null);
                    $this->planetFieldRepository->save($planetField);
                }
            }
        } else {
            $field->setReactivateAfterUpgrade(null);
            $this->planetFieldRepository->save($field);
        }
    }
}
