<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAlertLevel;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowAlertLevel implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALVL';

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

        $game->setPageTitle(_('Alarmstufe ändern'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_alvl');

        $game->setTemplateVar('SHIP', $ship);
    }
}
