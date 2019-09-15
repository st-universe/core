<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuilding;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ActivateBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE';

    private $colonyLoader;

    private $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            (int) request::indInt('fid')
        );
        if ($field === null) {
            return;
        }

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

        $this->planetFieldRepository->save($field);

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
