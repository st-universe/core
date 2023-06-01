<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManager;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

final class BuildingAction implements BuildingActionInterface
{
    private ColonyStorageManager $colonyStorageManager;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        ColonyStorageManager $colonyStorageManager,
        BuildingManagerInterface $buildingManager
    ) {
        $this->colonyStorageManager = $colonyStorageManager;
        $this->buildingManager = $buildingManager;
    }

    public function activate(ColonyInterface $colony, PlanetFieldInterface $field, GameControllerInterface $game): void
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
        if ($colony->getWorkless() < $field->getBuilding()->getWorkers()) {
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

    public function deactivate(ColonyInterface $colony, PlanetFieldInterface $field, GameControllerInterface $game): void
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
        ColonyInterface $colony,
        PlanetFieldInterface $field,
        GameControllerInterface $game,
        bool $upgrade = false
    ): void {
        if (!$field->hasBuilding()) {
            return;
        }

        $building = $field->getBuilding();

        if (!$building->isRemovable() && $upgrade === false) {
            return;
        }

        $this->buildingManager->remove($field, $upgrade);

        $game->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );

        $game->addInformation(_('Es konnten folgende Waren recycled werden'));

        foreach ($building->getCosts() as $value) {
            $halfAmount = $value->getHalfAmount();
            if ($colony->getStorageSum() + $halfAmount > $colony->getMaxStorage()) {
                $amount = $colony->getMaxStorage() - $colony->getStorageSum();
            } else {
                $amount = $halfAmount;
            }
            if ($amount <= 0) {
                $game->addInformation(_('[b][color=#ff2626]Keine weiteren Lagerkapazitäten vorhanden![/color][/b]'));
                break;
            }
            $this->colonyStorageManager->upperStorage($colony, $value->getCommodity(), $amount);

            $game->addInformationf('%d %s', $amount, $value->getCommodity()->getName());
        }
    }
}
