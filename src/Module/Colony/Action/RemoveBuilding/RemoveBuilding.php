<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RemoveBuilding;

use ColfieldData;
use Colfields;
use ColonyData;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class RemoveBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_REMOVE_BUILDING';

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

    protected function deActivateBuilding(ColfieldData $field, ColonyData $colony, GameControllerInterface $game)
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
