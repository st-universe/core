<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

final class BuildingAction implements BuildingActionInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private BuildingManagerInterface $buildingManager,
        private BuildingActionEffects $buildingActionEffects
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
            if (!$this->buildingActionEffects->canActivateOnColony($host, $building, $game)) {
                return;
            }

            $this->buildingActionEffects->handleActivation($building, $host);
        }

        if (!$this->buildingManager->activate($field)) {
            return;
        }

        if ($host instanceof Colony) {
            $this->buildingActionEffects->registerForBuilding($host, $building, 1);
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
            $this->buildingActionEffects->handleDeactivation($building, $host, $game);
        }

        $this->buildingManager->deactivate($field);

        if ($host instanceof Colony && !$field->isActive()) {
            $this->buildingActionEffects->registerForBuilding($host, $building, -1);
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
            $this->buildingActionEffects->handleRemovalBeforeDemolition($field, $building, $host, $isDueToUpgrade, $game);
        }

        $this->buildingManager->remove($field, $isDueToUpgrade);

        if ($host instanceof Colony && $wasActive && !$field->isActive()) {
            $this->buildingActionEffects->registerForBuilding($host, $building, -1);
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

    private function canRemoveBuilding(Building $building, bool $isDueToUpgrade): bool
    {
        return $isDueToUpgrade || $building->isRemovable();
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

}
