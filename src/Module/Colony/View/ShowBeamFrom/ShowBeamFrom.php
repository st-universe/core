<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamFrom;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowBeamFrom implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMFROM';

    private $colonyLoader;

    private $showBeamFromRequest;

    private $shipRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBeamFromRequestInterface $showBeamFromRequest,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBeamFromRequest = $showBeamFromRequest;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBeamFromRequest->getColonyId(),
            $userId
        );

        $target = $this->shipRepository->find($this->showBeamFromRequest->getShipId());
        if ($target === null) {
           return;
        }

        if (!checkColonyPosition($colony,$target) || ($target->getCloakState() && !$target->ownedByCurrentUser())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Von Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/show_ship_beamfrom');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}
