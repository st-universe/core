<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamTo;

use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowBeamTo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMTO';

    private ColonyLoaderInterface $colonyLoader;

    private ShowBeamToRequestInterface $showBeamToRequest;

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBeamToRequestInterface $showBeamToRequest,
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBeamToRequest = $showBeamToRequest;
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showBeamToRequest->getColonyId(),
            $userId,
            false
        );

        $game->setPageTitle(_('Zu Schiff beamen'));
        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $wrapper = $this->shipLoader->find($this->showBeamToRequest->getShipId(), false);
        if ($wrapper === null) {
            return;
        }
        $target = $wrapper->get();

        if (!$this->interactionChecker->checkColonyPosition($colony, $target) || ($target->getCloakState() && $target->getUser() !== $user)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/show_ship_beamto');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}
