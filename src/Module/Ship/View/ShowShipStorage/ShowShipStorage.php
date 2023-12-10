<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipStorage;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShipStorage implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPSTORAGE';

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
            $userId,
            true,
            false
        );

        $game->setPageTitle(_('Schiffsfracht'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/shipstorage');

        $game->setTemplateVar('SHIP', $ship);
    }
}
