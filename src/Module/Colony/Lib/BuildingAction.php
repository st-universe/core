<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;

final class BuildingAction implements BuildingActionInterface
{
    public function __construct(private StorageManagerInterface $storageManager, private BuildingManagerInterface $buildingManager) {}

    #[Override]
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
            $game->addInformationf(
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
            $game->addInformationf(
                _('Zum Aktivieren des Gebäudes (%s) werden %s Arbeiter benötigt'),
                $building->getName(),
                $building->getWorkers()
            );
            return;
        }

        $this->buildingManager->activate($field);

        $game->addInformationf(
            _('%s auf Feld %s wurde aktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    #[Override]
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

        $game->addInformationf(
            _('%s auf Feld %s wurde deaktiviert'),
            $building->getName(),
            $field->getFieldId()
        );
    }

    #[Override]
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

        $game->addInformationf(
            _('%s auf Feld %d wurde demontiert'),
            $building->getName(),
            $field->getFieldId()
        );


        $host = $field->getHost();
        if ($host instanceof ColonySandbox) {
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
            $this->storageManager->upperStorage($host, $value->getCommodity(), $amount);

            $game->addInformationf('%d %s', $amount, $value->getCommodity()->getName());
        }
    }
}
