<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowEpsTransfer;

use request;
use Ship;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowEpsTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ETRANSFER';

    private $shipLoader;

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

        $target = new Ship(request::getIntFatal('target'));
        if ($ship->canInteractWith($target) === false) {
            // @todo ships cant interact
        }

        $game->setPageTitle("Energietransfer");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/show_ship_etransfer');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
    }
}
