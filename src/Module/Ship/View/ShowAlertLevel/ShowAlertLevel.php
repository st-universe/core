<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAlertLevel;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowAlertLevel implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALVL';

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

        $game->setPageTitle(_('Alarmstufe ändern'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/shipmacros.xhtml/show_ship_alvl');

        $game->setTemplateVar('SHIP', $ship);
    }
}
