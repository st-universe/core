<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipDetails;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShipDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPDETAILS';

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
            true
        );

        $game->setPageTitle(_('Schiffsinformationen'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/shipdetails');

        $game->setTemplateVar('SHIP', $ship);
    }
}
