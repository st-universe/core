<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingAction implements BuildingActionInterface
{
    private $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function activate(ColonyData $colony, PlanetFieldInterface $field, GameControllerInterface $game): void
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
            $game->addInformation("Das Gebäude kann aufgrund zu starker Beschädigung nicht aktiviert werden");
            return;
        }
        if ($colony->getWorkless() < $field->getBuilding()->getWorkers()) {
            $game->addInformation("Zum aktivieren des Gebäudes werden " . $field->getBuilding()->getWorkers() . " Arbeiter benötigt");
            return;
        }
        $colony->lowerWorkless($field->getBuilding()->getWorkers());
        $colony->upperWorkers($field->getBuilding()->getWorkers());
        $colony->upperMaxBev($field->getBuilding()->getHousing());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $colony->save();
        $field->getBuilding()->postActivation($colony);

        $game->addInformation($field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " wurde aktiviert");
    }

    public function deactivate(ColonyData $colony, PlanetFieldInterface $field, GameControllerInterface $game): void
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
        $colony->upperWorkless($field->getBuilding()->getWorkers());
        $colony->lowerWorkers($field->getBuilding()->getWorkers());
        $colony->lowerMaxBev($field->getBuilding()->getHousing());
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $colony->save();
        $field->getBuilding()->postDeactivation($colony);

        $game->addInformation($field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " wurde deaktiviert");
    }
}