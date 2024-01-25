<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

final class BuildingAction implements BuildingActionInterface
{
    private ColonyStorageManagerInterface $colonyStorageManager;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        ColonyStorageManagerInterface $colonyStorageManager,
        BuildingManagerInterface $buildingManager
    ) {
        $this->colonyStorageManager = $colonyStorageManager;
        $this->buildingManager = $buildingManager;
    }

    public function activate(PlanetFieldInterface $field, GameControllerInterface $game): void
    {
        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->isActivateAble()) {
            return;
        }
        if ($field->isActive()) {
            return;
        }
        if ($field->hasHighDamage()) {
            $game->addInformationf(
                _('Das Gebäude (%s) kann aufgrund zu starker Beschädigung nicht aktiviert werden'),
                $field->getBuilding()->getName()
            );
            return;
        }

        $host = $field->getHost();
        if (
            $host instanceof ColonyInterface
            && $host->getWorkless() < $field->getBuilding()->getWorkers()
        ) {
            $game->addInformationf(
                _('Zum Aktivieren des Gebäudes (%s) werden %s Arbeiter benötigt'),
                $field->getBuilding()->getName(),
                $field->getBuilding()->getWorkers()
            );
            return;
        }

        $this->buildingManager->activate($field);

        $game->addInformationf(
            _('%s auf Feld %s wurde aktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function deactivate(PlanetFieldInterface $field, GameControllerInterface $game): void
    {
        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->isActivateAble()) {
            return;
        }
        if (!$field->isActive()) {
            return;
        }

        $this->buildingManager->deactivate($field);

        $game->addInformationf(
            _('%s auf Feld %s wurde deaktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function remove(
        PlanetFieldInterface $field,
        GameControllerInterface $game,
        bool $isDueToUpgrade = false
    ): void {

        if (!$field->hasBuilding()) {
            return;
        }

        $building = $field->getBuilding();

        if (!$isDueToUpgrade && !$building->isRemovable()) {
            return;
        }

        $this->buildingManager->remove($field, $isDueToUpgrade);

        $game->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );


        $host = $field->getHost();
        if ($host instanceof ColonySandboxInterface) {
            return;
        }

        $game->addInformation(_('Es konnten folgende Waren recycled werden'));

        foreach ($building->getCosts() as $value) {
            $halfAmount = $value->getHalfAmount();
            if ($host->getStorageSum() + $halfAmount > $host->getMaxStorage()) {
                $amount = $host->getMaxStorage() - $host->getStorageSum();
            } else {
                $amount = $halfAmount;
            }
            if ($amount <= 0) {
                $game->addInformation(_('[b][color=#ff2626]Keine weiteren Lagerkapazitäten vorhanden![/color][/b]'));
                break;
            }
            $this->colonyStorageManager->upperStorage($host, $value->getCommodity(), $amount);

            $game->addInformationf('%d %s', $amount, $value->getCommodity()->getName());
        }
    }
}
