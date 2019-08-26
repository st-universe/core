<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\UpgradeBuilding;

use BuildingFieldAlternative;
use BuildingUpgrade;
use ColfieldData;
use Colfields;
use ColonyData;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class UpgradeBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_UPGRADE_BUILDING';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

        $upgrade = new BuildingUpgrade(request::getIntFatal('upid'));
        if ($upgrade->getUpgradeFrom() != $field->getBuildingId()) {
            return;
        }
        if (!currentUser()->hasResearched($upgrade->getResearchId())) {
            return;
        }
        if ($field->isInConstruction()) {
            $game->addInformation(_('Das Gebäude auf diesem Feld ist noch nicht fertig'));
            return;
        }
        $storage = $colony->getStorage();
        foreach ($upgrade->getCost() as $key => $obj) {
            if (!array_key_exists($obj->getGoodId(), $storage)) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    getGoodName($obj->getGoodId())
                );
                return;
            }
            if ($obj->getAmount() > $storage[$obj->getGoodId()]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $obj->getAmount(),
                    getGoodName($obj->getGoodId()),
                    $storage[$obj->getGoodId()]->getAmount()
                );
                return;
            }
        }

        if ($colony->getEps() < $upgrade->getEnergyCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $upgrade->getEnergyCost(),
                $colony->getEps()
            );
            return;
        }


        $colony->lowerEps($upgrade->getEnergyCost());
        $this->removeBuilding($field, $colony, $game);

        foreach ($upgrade->getCost() as $key => $obj) {
            $colony->lowerStorage($obj->getGoodId(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = BuildingFieldAlternative::getByBuildingField($upgrade->getBuilding()->getId(),
            $field->getFieldType());
        if ($alt_building) {
            $building = $alt_building->getAlternateBuilding();
        }

        $field->setBuildingId($upgrade->getBuilding()->getId());
        $field->setBuildtime($upgrade->getBuilding()->getBuildtime());
        $colony->save();
        $field->save();

        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $upgrade->getDescription(),
            $field->getBuildtimeDisplay()
        );
    }

    private function removeBuilding(ColfieldData $field, ColonyData $colony, GameControllerInterface $game)
    {
        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->getBuilding()->isRemoveAble()) {
            return;
        }
        $this->deActivateBuilding($field, $colony, $game);
        $colony->lowerMaxStorage($field->getBuilding()->getStorage());
        $colony->lowerMaxEps($field->getBuilding()->getEpsStorage());
        $game->addInformationf(
            _('%s auf Feld %s wurde demontiert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
        $game->addInformation(_('Es konnten folgende Waren recycled werden'));
        foreach ($field->getBuilding()->getCosts() as $key => $value) {
            if ($colony->getStorageSum() + $value->getHalfCount() > $colony->getMaxStorage()) {
                $amount = $colony->getMaxStorage() - $colony->getStorageSum();
            } else {
                $amount = $value->getHalfCount();
            }
            if ($amount <= 0) {
                break;
            }
            $colony->upperStorage($value->getGoodId(), $amount);
            $game->addInformationf('%d %s', $amount, $value->getGood()->getName());
        }
        $field->clearBuilding();
        $field->save();
        $colony->save();
    }

    protected function deActivateBuilding(Colfields $field, ColonyData $colony, GameControllerInterface $game)
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
        $field->save();
        $colony->save();
        $field->getBuilding()->postDeactivation($colony);

        $game->addInformationf(
            _('%s auf Feld %d wurde deaktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
