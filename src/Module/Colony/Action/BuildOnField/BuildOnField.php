<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use Building;
use BuildingFieldAlternative;
use ColfieldData;
use Colfields;
use ColonyData;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResult;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class BuildOnField implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_BUILD';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBuildResult::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

        if ($field->getTerraformingId() > 0) {
            return;
        }
        $building_id = request::indInt('bid');
        $building = new Building($building_id);
        if (!$game->getUser()->hasResearched($building->getResearchId())) {
            return;
        }
        if (!in_array($field->getFieldType(), $building->getBuildableFields())) {
            return;
        }
        if ($building->hasLimitColony() && Colfields::countInstances('buildings_id=' . $building->getId() . ' AND colonies_id=' . $colony->getId()) >= $building->getLimitColony()) {
            $game->addInformation("Dieses Gebäude kann auf dieser Kolonie nur " . $building->getLimitColony() . " mal gebaut werden");
            return;
        }
        if ($building->hasLimit() && Colfields::countInstances('buildings_id=' . $building->getId() . ' AND colonies_id IN (SELECT id FROM stu_colonies WHERE user_id=' . $game->getUser()->getId() . ')') >= $building->getLimit()) {
            $game->addInformation("Dieses Gebäude kann insgesamt nur " . $building->getLimit() . " mal gebaut werden");
            return;
        }
        $storage = $colony->getStorage();
        foreach ($building->getCosts() as $key => $obj) {
            if ($field->hasBuilding()) {
                if (!array_key_exists($key, $storage) && !array_key_exists($key,
                        $field->getBuilding()->getCosts())) {
                    $game->addInformation("Es werden " . $obj->getAmount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Es ist jedoch keines vorhanden");
                    return;
                }
            } else {
                if (!array_key_exists($key, $storage)) {
                    $game->addInformation("Es werden " . $obj->getAmount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Es ist jedoch keines vorhanden");
                    return;
                }
            }
            if (!isset($storage[$key])) {
                $amount = 0;
            } else {
                $amount = $storage[$key]->getAmount();
            }
            if ($field->hasBuilding()) {
                $goods = $field->getBuilding()->getCosts();
                if (isset($goods[$key])) {
                    $amount += $goods[$key]->getHalfCount();
                }
            }
            if ($obj->getAmount() > $amount) {
                $game->addInformation("Es werden " . $obj->getAmount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Vorhanden sind nur " . $amount);
                return;
            }
        }

        if ($colony->getEps() < $building->getEpsCost()) {
            $game->addInformation("Zum Bau wird " . $building->getEpsCost() . " Energie benötigt - Vorhanden ist nur " . $colony->getEps());
            return;
        }

        if ($field->hasBuilding()) {
            if ($colony->getEps() > $colony->getMaxEps() - $field->getBuilding()->getEpsStorage()) {
                if ($colony->getMaxEps() - $field->getBuilding()->getEpsStorage() < $building->getEpsCost()) {
                    $game->addInformation("Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung");
                    return;
                }
            }
            $this->removeBuilding($field, $colony, $game);
        }

        foreach ($building->getCosts() as $key => $obj) {
            $colony->lowerStorage($obj->getGoodId(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = BuildingFieldAlternative::getByBuildingField($building->getId(),
            $field->getFieldType());
        if ($alt_building) {
            $building = $alt_building->getAlternateBuilding();
        }

        $colony->lowerEps($building->getEpsCost());
        $field->setBuildingId($building->getId());
        $field->setBuildtime($building->getBuildtime());
        $colony->save();
        $field->save();
        $game->addInformation($building->getName() . " wird gebaut - Fertigstellung: " . $field->getBuildtimeDisplay());
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
        $game->addInformation($field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " wurde demontiert");
        $game->addInformation("Es konnten folgende Waren recycled werden");
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
            $game->addInformation($amount . " " . $value->getGood()->getName());
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

        $game->addInformation($field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " wurde deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
