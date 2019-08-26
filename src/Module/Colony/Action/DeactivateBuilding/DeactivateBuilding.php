<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuilding;

use Colfields;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class DeactivateBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEACTIVATE';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);
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
