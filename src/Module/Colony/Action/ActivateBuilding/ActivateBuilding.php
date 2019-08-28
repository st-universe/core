<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use Colfields;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
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
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $field = Colfields::getByColonyField(
            (int) request::indInt('fid'),
            $colony->getId()
        );

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
            $game->addInformation(_('Das Gebäude kann aufgrund zu starker Beschädigung nicht aktiviert werden'));
            return;
        }
        if ($colony->getWorkless() < $field->getBuilding()->getWorkers()) {
            $game->addInformationf(
                _('Zum aktivieren des Gebäudes werden %d Arbeiter benötigt'),
                $field->getBuilding()->getWorkers()
            );
            return;
        }
        $colony->lowerWorkless($field->getBuilding()->getWorkers());
        $colony->upperWorkers($field->getBuilding()->getWorkers());
        $colony->upperMaxBev($field->getBuilding()->getHousing());
        $field->setActive(1);
        $field->save();
        $colony->save();
        $field->getBuilding()->postActivation($colony);

        $game->addInformationf(
            _('%s auf Feld %d wurde aktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
