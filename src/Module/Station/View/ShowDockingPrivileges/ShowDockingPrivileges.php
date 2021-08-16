<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingPrivileges;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowDockingPrivileges implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_LIST';

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

        $game->showMacro('html/stationmacros.xhtml/dockprivilegelist');

        $game->setTemplateVar('SHIP', $ship);
    }
}
