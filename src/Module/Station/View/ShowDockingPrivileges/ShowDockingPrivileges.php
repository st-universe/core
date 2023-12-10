<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingPrivileges;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\Lib\DockingPrivilegeItem;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;

final class ShowDockingPrivileges implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_LIST';

    private ShipLoaderInterface $shipLoader;

    private StationUiFactoryInterface $stationUiFactory;

    public function __construct(
        StationUiFactoryInterface $stationUiFactory,
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
        $this->stationUiFactory = $stationUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->showMacro('html/stationmacros.xhtml/dockprivilegelist');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'DOCKING_PRIVILEGES',
            $ship->getDockPrivileges()->map(
                fn (DockingPrivilegeInterface $dockingPrivilege): DockingPrivilegeItem =>
                $this->stationUiFactory->createDockingPrivilegeItem($dockingPrivilege)
            )
        );
    }
}
