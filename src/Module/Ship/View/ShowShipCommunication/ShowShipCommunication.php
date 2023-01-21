<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipCommunication;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShipCommunication implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_COMMUNICATION';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Schiffsinformationen'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/shipcommunication');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'TEMPLATETEXT',
            sprintf('Die %s in Sektor %s sendet folgende Broadcast Nachricht:', $ship->getName(), $ship->getSectorString())
        );
    }
}
