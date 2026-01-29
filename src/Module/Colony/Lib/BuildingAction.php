<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;

final class BuildingAction implements BuildingActionInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private BuildingManagerInterface $buildingManager,
        private ColonyLibFactoryInterface $colonyLibFactory
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
            $logisticsCommodityId = CommodityTypeConstants::COMMODITY_EFFECT_SHIPYARD_LOGISTICS;
            $logisticsConsumption = 0;

            foreach ($building->getCommodities() as $buildingCommodity) {
                if ($buildingCommodity->getCommodityId() === $logisticsCommodityId) {
                    $logisticsConsumption = $buildingCommodity->getAmount();
                    break;
                }
            }

            if ($logisticsConsumption < 0) {
                $production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
                $currentProduction = array_key_exists($logisticsCommodityId, $production)
                    ? $production[$logisticsCommodityId]->getProduction()
                    : 0;
                $projectedProduction = $currentProduction + $logisticsConsumption;

                if ($projectedProduction < 0) {
                    $game->getInfo()->addInformationf(
                        _('Das Gebäude (%s) kann nicht aktiviert werden, da nicht genug Werftlogistik zur Verfügung steht'),
                        $building->getName()
                    );
                    return;
                }
            }
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

        $this->buildingManager->remove($field, $isDueToUpgrade);

        $game->getInfo()->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );


        $host = $field->getHost();
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
}
