<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use Colfields;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ActivateBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ACTIVATE';

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

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField($fieldId, $colony->getId());

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
        $field->save();
        $colony->save();
        $field->getBuilding()->postActivation($colony);

        $game->addInformation($field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " wurde aktiviert");

        $game->setView(ShowColony::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
