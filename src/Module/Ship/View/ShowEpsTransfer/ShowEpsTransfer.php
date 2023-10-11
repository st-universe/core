<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowEpsTransfer;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowEpsTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ETRANSFER';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $game->setPageTitle("Energietransfer");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/entity_not_available');

        if (!InteractionChecker::canInteractWith($ship, $target, $game, false, true)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_etransfer');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('WRAPPER', $wrapper);
    }
}
