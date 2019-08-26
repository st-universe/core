<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamFrom;

use AccessViolation;
use Ship;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBeamFrom implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMFROM';

    private $colonyLoader;

    private $showBeamFromRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBeamFromRequestInterface $showBeamFromRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBeamFromRequest = $showBeamFromRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBeamFromRequest->getColonyId(),
            $userId
        );

        $target = new Ship($this->showBeamFromRequest->getShipId());

        if (!checkPosition($colony,$target) || ($target->cloakIsActive() && !$target->ownedByCurrentUser())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Von Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/show_ship_beamfrom');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}
