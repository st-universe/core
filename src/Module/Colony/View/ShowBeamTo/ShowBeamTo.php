<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamTo;

use AccessViolation;
use Ship;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBeamTo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMTO';

    private $colonyLoader;

    private $showBeamToRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBeamToRequestInterface $showBeamToRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBeamToRequest = $showBeamToRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBeamToRequest->getColonyId(),
            $userId
        );

        $target = new Ship($this->showBeamToRequest->getShipId());

        if (!checkPosition($colony,$target) || ($target->cloakIsActive() && !$target->ownedByCurrentUser())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Zu Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/show_ship_beamto');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}
