<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingAction implements BuildingActionInterface
{
    private $planetFieldRepository;

    private $colonyRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
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
        $this->colonyRepository->save($colony);

        $field->getBuilding()->postActivation($colony);

        $colony->clearCache();

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
        $colony->upperWorkless($field->getBuilding()->getWorkers());
        $colony->lowerWorkers($field->getBuilding()->getWorkers());
        $colony->lowerMaxBev($field->getBuilding()->getHousing());
        $field->setActive(0);

        $this->planetFieldRepository->save($field);
        $this->colonyRepository->save($colony);

        $field->getBuilding()->postDeactivation($colony);

        $colony->clearCache();

        $game->addInformationf(
            _('%s auf Feld %s wurde deaktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }
}