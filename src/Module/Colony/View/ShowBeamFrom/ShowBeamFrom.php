<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamFrom;

use Stu\Exception\AccessViolation;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowBeamFrom implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMFROM';

    private ColonyLoaderInterface $colonyLoader;

    private ShowBeamFromRequestInterface $showBeamFromRequest;

    private ShipLoaderInterface $shipLoader;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBeamFromRequestInterface $showBeamFromRequest,
        ShipLoaderInterface $shipLoader,
        PositionCheckerInterface $positionChecker
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBeamFromRequest = $showBeamFromRequest;
        $this->shipLoader = $shipLoader;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBeamFromRequest->getColonyId(),
            $userId
        );

        $target = $this->shipLoader->find($this->showBeamFromRequest->getShipId());
        if ($target === null) {
            return;
        }

        if (!$this->positionChecker->checkColonyPosition($colony, $target)) {
            throw new AccessViolation(sprintf(_('Target-shipId %d is not at same position as colonyId %d'), $target->getId(), $colony->getId()));
        }

        if (($target->getCloakState() && $target->getUser() !== $user)) {
            throw new AccessViolation(sprintf(_('Target-shipId %d is cloaked, colonyId %d'), $target->getId(), $colony->getId()));
        }

        $game->setPageTitle(_('Von Schiff beamen'));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/show_ship_beamfrom');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}
